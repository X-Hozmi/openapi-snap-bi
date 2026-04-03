<?php

namespace App\Helpers;

class DataTypeCaster
{
    public function recursiveArrayToObject(mixed $data): mixed
    {
        if (is_array($data)) {
            if ($data === []) {
                return [];
            }

            $isAssoc = array_keys($data) !== range(0, count($data) - 1);

            if ($isAssoc) {
                $object = new \stdClass;
                foreach ($data as $key => $value) {
                    $object->{$key} = $this->recursiveArrayToObject($value);
                }

                return $object;
            }

            return array_map(fn ($v) => $this->recursiveArrayToObject($v), $data);
        }

        if (is_object($data)) {
            $properties = get_object_vars($data);
            foreach ($properties as $key => $value) {
                $data->{$key} = $this->recursiveArrayToObject($value);
            }

            return $data;
        }

        return $data;
    }
}
