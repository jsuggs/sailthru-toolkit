<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TemplateStatisticsReportCommand extends AbstractSailThruCommand
{
    const DATE_API_FORMAT = 'Y-m-d';
    const DATE_OUTPUT_FORMAT = 'Ymd';
    const DATE_REPORT_FORMAT = 'm/d/Y';

    protected $fh;
    protected $api;
    protected $dateStart;
    protected $dateEnd;
    protected $dateDiff;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate a CSV report for template statistics')
            ->addArgument('env',        InputArgument::REQUIRED, 'The env')
            ->addArgument('template',  InputArgument::REQUIRED, 'The template')
            ->addArgument('date_start', InputArgument::REQUIRED, 'Date Start')
            ->addArgument('date_end',   InputArgument::REQUIRED, 'Date End')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->api = $this->getSailThruClient($input->getArgument('env'));

        $this->dateStart = new \DateTime($input->getArgument('date_start'));
        $this->dateEnd = new \DateTime($input->getArgument('date_end'));
        $this->dateDiff = $this->dateStart->diff($this->dateEnd);

        $fileName = sprintf('%s/SailThruReports/%s.csv',
            sys_get_temp_dir(),
            $input->getArgument('template')
        );
        $output->writeln(sprintf('Creating file: %s', $fileName));
        $this->fh = fopen($fileName, 'w+');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Write the headers
        fputcsv($this->fh, array('Date', 'Sends', 'Opens', 'Clicks', 'Purchases', 'Revenue', 'Opt Outs', 'Spam'));

        for ($x = 0; $x < $this->dateDiff->days; $x++) {
            $start = clone $this->dateStart;
            $start->modify(sprintf('+%d days', $x));
            $end = clone $start;
            $end->modify('+1 days');

            $output->writeln(sprintf('Pulling stats for %s', $start->format(self::DATE_OUTPUT_FORMAT)));

            $stats = $this->api->stats_send(
                $input->getArgument('template'),
                $start->format(self::DATE_API_FORMAT),
                $end->format(self::DATE_API_FORMAT),
                array(
                    'click_times' => 0,
                    'engagement' => 1,
                )
            );

            if (isset($stats['error'])) {
                continue;
            }

            fputcsv($this->fh, array(
                $start->format(self::DATE_REPORT_FORMAT),
                isset($stats['count']) ? $stats['count'] : 0,
                isset($stats['open_total']) ? $stats['open_total'] : 0,
                isset($stats['click_total']) ? $stats['click_total'] : 0,
                isset($stats['purchase']) ? $stats['purchase'] : 0,
                $this->formatCurrency(isset($stats['purchase_price']) ? $stats['purchase_price'] : 0),
                isset($stats['engagement']['optout']) && isset($stats['engagement']['optout']['count']) ? $stats['engagement']['optout']['count'] : 0,
                isset($stats['spam']) ? $stats['spam'] : 0,
            ));
        }

        fclose($this->fh);
    }

    public function getCommandName()
    {
        return 'template-statistics';
    }
}
