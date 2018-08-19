<?php

/**
 * @file
 * Contains \Drupal\site_staff\Controller\StaffController
 */

namespace Drupal\site_staff\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Vocabulary;

class StaffController extends ControllerBase {

    /**
     * Страница отображения содержимого страницы с персоналом.
     */
    public static function view($limit = 0, $tid = 0) {
        $build = array();

        $db = \Drupal::database();
        $query = $db->select('node_field_data', 'n');
        $query->condition('n.status', 1);
        $query->condition('n.type', 'staff');
        $query->fields('n', array('nid'));
        $query->orderBy('title', 'DESC');

        if ($tid) {
            $query->innerJoin('taxonomy_index', 't', 'n.nid=t.nid');
            $query->condition('t.tid', $tid);
        }

        if ($limit) {
            $query->range(0, $limit);
            $result = $query->execute();
        } else {
            $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(100);
            $result = $pager->execute();
        }

        $nids = [];
        foreach ($result as $row) {
            $nids[] = $row->nid;
        }

        if (!empty($nids)) {
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

            // Создает HTML отображение материалов.
            $build['staff'] = array(
                '#theme' => 'staff',
                '#nodes' => $nodes,
            );

            // Добавляет пейджер на страницу.
            if (!$limit) {
                $build['pager'] = array(
                    '#type' => 'pager',
                );
            }
        }

        return $build;
    }

    /**
     * Заголовок.
     */
    public function getTitle() {
        $vocabulary = Vocabulary::load('staff');
        return $vocabulary->label();
    }
}