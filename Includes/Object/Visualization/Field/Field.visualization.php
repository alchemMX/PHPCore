<?php

/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

namespace Visualization\Field;

/**
 * Field
 */
class Field extends \Visualization\Visualization
{
    /**
     * @var array $type Input types
     */
    private array $type = [
        'id' => 'Id',
        'date'=> 'Date',
        'icon' => 'Icon',
        'text' => 'Text',
        'file' => 'File',
        'user' => 'User',
        'field' => 'Field',
        'email' => 'Text',
        'radio' => 'Radio',
        'number' => 'Text',
        'select' => 'Select',
        'button' => 'Button',
        'password' => 'Password',
        'textarea' => 'Textarea',
        'checkbox' => 'Checkbox'
    ];

    /**
     * @var array $button Default field buttons
     */
    private array $button = [
        'submit' => [
            'data' => [
                'name' => 'submit',
                'type' => 'submit',
                'value' => 'L_SUBMIT'
            ]
        ]
    ];

    /**
     * @var array $data Data which will be used in form
     */
    private array $data = [];

    /**
     * @var array $allowButtons If true - under fields will be displayed buttons
     */
    private bool $allowButtons = true;

    /**
     * Disables buttons
     *
     * @return void
     */
    public function disButtons()
    {
        $this->allowButtons = false;
    }

    /**
     * Adds data to form
     *
     * @param  array $data
     * 
     * @return void
     */
    public function data( array $data )
    {
        $this->data = $data;
    }

    /**
     * Sets value to current row data
     *
     * @param  mixed $value Value
     * 
     * @return $this
     */
    public function setValue( mixed $value )
    {   
        $this->data[$this->list[1]] = $value;

        return $this;
    }

    /**
     * This function will be executed before returning fields data
     *
     * @return void
     */
    protected function clb_getData()
    {
        foreach ($this->obj->get->body() as $object => $data) { $this->object($object);

            foreach ($this->obj->get->body() as $row => $data) { $this->row($row);
                
                if ($this->obj->is->data('href')) {
                    switch (substr($this->obj->get->data('href'), 0, 1)) {
            
                        case '$':
                            $this->obj->set->data('href', substr($this->obj->get->data('href'), 1));
                        break;
    
                        case '~':
                            $this->obj->set->data('href', $this->system->url->build(substr($this->obj->get->data('href'), 1)));
                        break;
        
                        default:
                            $this->obj->set->data('href', $this->system->url->build(URL . $this->obj->get->data('href')));
                        break;
                    }
                }

                if (!in_array($this->obj->get->options('type'), array_keys($this->type)) and $this->obj->is->template('option') === false) {
                    $this->obj->set->delete->delete();
                    continue;
                }

                if ($this->obj->is->template('option') === false) {
                    $this->obj->set->template('option', ROOT. $this->templatePath . '/Type/' . $this->type[$this->obj->get->options('type')] . '.phtml');
                }

                $value = $this->obj->get->data('value') ?: $row;

                if ($this->obj->get->options('type') !== 'password') {

                    switch ($this->obj->get->options('type')) {

                        case 'text':
                            $this->obj->set->data('value', (string)($this->data[$value] ?? ''));
                        break;

                        case 'number':

                            if ($this->data[$value] == 0) {
                                $this->data[$value] = '';
                            } else {
                                $this->data[$value] = (int)$this->data[$value];
                            }

                            $this->obj->set->data('value', $this->data[$value] ?? '');
                        break;

                        default:
                            $this->obj->set->data('value', $this->data[$value] ?? '');
                        break;
                    }    
                }

                foreach ($this->obj->get->body() as $option => $data) { $this->option($option);
                    
                    if (is_array($checked = $this->obj->get->options('checked'))) {

                        foreach ($checked as $cond => $value) {

                            switch ($cond) {

                                case 'in': 
                                    if (in_array($this->obj->get->data('value'), (array)$this->data[$value])) {
                                        $this->obj->set->options('checked', true);
                                        continue 3;
                                    }
                                break;

                                case 'empty': {
                                    
                                    if (empty($this->data[$value])) {

                                        $this->obj->set->options('checked', true);
                                        continue 3;
                                    }
                                break;
                                }

                                case 'filled':
                                    if (!empty($this->data[$value])) {
                                        $this->obj->set->options('checked', true);
                                        continue 3;
                                    }
                                break;

                            }
                        }
                        continue;
                    } else {

                        if (isset($this->data[$value])) {
                            if ($this->obj->get->data('value') == $this->data[$value]) {
                                $this->obj->set->options('checked', true);
                            }
                        }
                    }
                }
            }
        }

        $this->object = [
            'body' => $this->object['body'],
            '_data' => $this->data,
            'button' => (bool)$this->allowButtons === true ? $this->object['button'] ?? $this->button : []
        ];
    }
}