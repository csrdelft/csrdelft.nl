<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2018
 */
abstract class BaseModel extends Model
{
    public function getUUID()
    {
        return strtolower(sprintf(
            '%s@%s.csrdelft.nl',
            $this->getKey(),
            \common\short_class($this)
        ));
    }

    public function jsonSerialize()
    {
        $array = parent::jsonSerialize();
        $array['UUID'] = $this->getUUID();

        return $array;
    }
}