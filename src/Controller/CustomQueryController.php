<?php

namespace Drupal\custom_query_module\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CustomQueryController {
    public function exportCsv($param) {
        // Define your static query

        $database = \Drupal::database();
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $uid= $user->get('uid')->value;
        $node = \Drupal::routeMatch()->getRawParameter('node');
        $nodeval = strval($node);
        $buildquery = "
            -- ADD SQL HERE
            -- Don't Forget The $param
            ";
        $result = $database->query($buildquery);

        // Prepare CSV content
        $csv_data = [];
        foreach ($result as $row) {
            $csv_data[] = (array) $row;
        }

        $date = date('YmdHis', time());

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="filename'.$date.'.csv"');

        // Write the CSV content
        $handle = fopen('php://temp', 'rw');
        if ($handle) {
            foreach ($csv_data as $line) {
                fputcsv($handle, $line);
            }
            rewind($handle);
            
            // Fetch the CSV content from the stream
            $csv_content = stream_get_contents($handle);
            fclose($handle);
            
            $response->setContent($csv_content);
        }

        return $response;
    }
}
