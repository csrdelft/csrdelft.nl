<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableResponse;

class LedenMemoryScoreResponse extends DataTableResponse
{

    private $titles = array();

    public function renderElement($score)
    {
        $array = $score->jsonSerialize();

        $minutes = floor($score->tijd / 60);
        $seconds = $score->tijd % 60;
        $array['tijd'] = ($minutes < 10 ? '0' : '') . $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;

        $array['door_uid'] = ProfielRepository::getLink($score->door_uid, 'civitas');

        if (!isset($this->titles[$score->groep])) {
            $this->titles[$score->groep] = '';

            // Cache the title of the group
            $parts = explode('@', $score->groep);
            if (isset($parts[0], $parts[1])) {
                switch ($parts[1]) {

                    case 'verticale.csrdelft.nl':
                        $groep = ContainerFacade::getContainer()->get(VerticalenRepository::class)->retrieveByUUID($score->groep);
                        $this->titles[$score->groep] = 'Verticale ' . $groep->naam;
                        break;

                    case 'lichting.csrdelft.nl':
                        $this->titles[$score->groep] = 'Lichting ' . $parts[0];
                        break;
                }
            }
        }
        $array['groep'] = $this->titles[$score->groep];

        return $array;
    }

}
