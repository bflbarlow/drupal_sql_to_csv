<?php

namespace Drupal\custom_query_module\Controller;

use Symfony\Component\HttpFoundation\Response;

class CustomQueryController {
    public function exportCsv() {
        // Define your static query

        $database = \Drupal::database();
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $uid= $user->get('uid')->value;
        $buildquery = "
        Select 'id' as id, 'date' as date, 'user_id' as user_id
        Union All
        select al.id, al.date, al.user_id From AdvertisementLog al where al.user_id = ".$uid." 
        order by id desc";
        $result = $database->query($buildquery);

        // Prepare CSV content
        $csv_data = [];
        foreach ($result as $row) {
            $csv_data[] = (array) $row;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

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
