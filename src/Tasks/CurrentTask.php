<?php

declare(strict_types=1);

namespace MyApp\Tasks;

use Phalcon\Cli\Task;

class CurrentTask extends Task
{
    public function mainAction()
    {
        define('APP_PATH', BASE_PATH . "/files/");
        $file = fopen(APP_PATH . "annual-enterprise-survey-2021-financial-year-provisional-csv.csv", "r");
        while (($getData = fgetcsv($file, 10000, ",")) !== false) {

            $payload = [
                "data_year" => $getData[0],
                "data_units" => $getData[4],
                "data_value" => $getData[8],
            ];
            $collection = $this->mongo->data;
            $status = $collection->insertOne($payload);
        }
        print_r($status);
        fclose($file);
    }
    public function exportAction()
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $output = fopen("php://output", "w");
        fputcsv($output, array('Year', 'Units', 'Value'));

        $collection = $this->mongo->data;
        $status = $collection->find([]);
        foreach ($status as $robot) {
            $data[] = [
                'year' => $robot->data_year,
                'units' => $robot->data_units,
                'value' => $robot->data_value,
            ];
        }
        foreach ($data as $fields) {
            fputcsv($output, $fields);
        }
        fclose($output);
    }
}
