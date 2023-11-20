<?php

namespace ArcadaLabs\Utils;

use GFAPI;

abstract class GFUtils
{
    public static function getValueFromTextInput($field)
    {
        $label = strtolower($field['label']);
        $value = array('type'=>'none');

        if (str_contains($label, 'name')) {
            if (str_contains($label, 'last')) {
                $value = array('type'=>'last_name', 'id'=>$field['id']);
            } else {
                $value = array('type'=>'first_name', 'id'=>$field['id']);
            }
        } elseif (str_contains($label, 'address')) {
            if (str_contains($label, '3')) {
                $value = array('type' => 'address_line_3', 'id' => $field['id']);
            } else if (str_contains($label, '2')) {
                $value = array('type'=>'address_line_2', 'id'=>$field['id']);
            } else {
                $value = array('type'=>'street', 'id'=>$field['id']);
            }
        } elseif (str_contains($label, 'zip')) {
            $value = array('type'=>'zip', 'id'=>$field['id']);
        } elseif (str_contains($label, 'city')) {
            $value = array('type'=>'city', 'id'=>$field['id']);
        } elseif (str_contains($label, 'state')) {
            $value = array('type'=>'state', 'id'=>$field['id']);
        } elseif (str_contains($label, 'country')) {
            $value = array('type'=>'country', 'id'=>$field['id']);
        } elseif (str_contains($label, 'email')) {
            $value = array('type'=>'email', 'id'=>$field['id']);
        } elseif (str_contains($label, 'phone')) {
            $value = array('type'=>'phone', 'id'=>$field['id']);
        }
        return $value;
    }

    /**
     * @param $field
     * @return array
     */
    public static function getNameFromInput($field): array
    {
        $fields = array();
        foreach ($field['inputs'] as $input) {
            $inputName = strtolower($input['label']);
            if (str_contains($inputName, 'first')) {
                $fields['first_name'] = $input['id'];
            }
            if (str_contains($inputName, 'last')) {
                $fields['last_name'] = $input['id'];
            }
        }
        return $fields;
    }

    /**
     * @param $field
     * @return array
     */
    public static function getAddressFromInput($field): array
    {
        $fields = array();
        foreach ($field['inputs'] as $input) {
            $inputName = strtolower($input['label']);
            if (str_contains($inputName, 'street')) {
                $fields['street'] = $input['id'];
                $fields['address_line_1'] = $input['id'];
            }
            if (str_contains($inputName, 'address_line_1')) {
                $fields['address_line_1'] = $input['id'];
            }
            if (str_contains($inputName, 'address_line_2')) {
                $fields['address_line_2'] = $input['id'];
            }
            if (str_contains($inputName, 'address_line_3')) {
                $fields['address_line_3'] = $input['id'];
            }
            if (str_contains($inputName, 'city')) {
                $fields['city'] = $input['id'];
            }
            if (str_contains($inputName, 'state')) {
                $fields['state'] = $input['id'];
            }
            if (str_contains($inputName, 'country')) {
                $fields['country'] = $input['id'];
            }
            if (str_contains($inputName, 'zip')) {
                $fields['zip'] = $input['id'];
            }
        }
        return $fields;
    }

    /**
     * Retrieves the fields for a constituent from the form
     * @param $formId
     * @return array
     */
    public static function getFieldsFromForm($formId): array
    {
        $form = GFAPI::get_form($formId);
        $fields = array();

        // we obtain the fields ids from the form
        foreach ($form['fields'] as $field) {
            switch ($field['type']) {
                case 'name':
                    $fields = array_merge($fields, self::getNameFromInput($field));
                    break;
                case 'email':
                    $fields['email'] = $field['id'];
                    break;
                case 'address':
                    $fields = array_merge($fields, self::getAddressFromInput($field));
                    break;
                case 'phone':
                    $fields['phone'] = $field['id'];
                    break;
                case 'total':
                    $fields['total'] = $field['id'];
                    break;
                case 'text':
                    $textValue = self::getValueFromTextInput($field);
                    if ($textValue['type'] !== 'none' && !array_key_exists($textValue['type'], $fields)) {
                        $fields[$textValue['type']] = $textValue['id'];
                    }
                    break;
                default:
                    break;
            }
        }

        return $fields;
    }

}
