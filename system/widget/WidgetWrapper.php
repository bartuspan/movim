<?php

/**
 * @file WidgetWrapper.php
 * This file is part of PROJECT.
 *
 * @brief Description
 *
 * @author Guillaume Pasquet <gpasquet@lewisday.co.uk>
 *
 * @version 1.0
 * @date 20 January 2011
 *
 * Copyright (C)2011 Lewis Day Transport Plc.
 *
 * All rights reserved.
 */

/**
 * A container that abstracts the communication between
 * widgets and the core of Movim.
 */
class WidgetWrapper
{
    private $register_widgets;
    private $all_widgets = array();
    private $loaded_widgets = array();
    private $loaded_widgets_old;
    private $registered_events = array();

    private static $instance;

    private $css = array(); // All the css loaded by the widgets so far.
    private $js = array(); // All the js loaded by the widgets so far.

    /**
     * Constructor. The parameter instructs the wrapper about whether
     * it should save $_SESSION or not.
     * @param $register set to false not to save the widgets in SESSION.
     */
    private function __construct($register)
    {
        $this->register_widgets = $register;
        $sess = Session::start(APP_NAME);
        $widgets = $sess->get('loaded_widgets');
        
        if(is_array($widgets)) {
            $this->loaded_widgets_old = $widgets;
        }
        
        $this->registered_events = $sess->get('registered_events');
        
        // We search all the existent widgets
        $this->all_widgets = array();
        
        $widgets_dir = scandir(APP_PATH ."widgets/");
        foreach($widgets_dir as $widget_dir) {
            if(is_dir(APP_PATH ."widgets/".$widget_dir) && 
                $widget_dir != '..' &&
                $widget_dir != '.')
               array_push($this->all_widgets, $widget_dir);         
        }
    }

    static function getInstance($register = true)
    {
        if(!is_object(self::$instance)) {
            self::$instance = new WidgetWrapper($register);
        }
        return self::$instance;
    }

    static function destroyInstance()
    {
        if(isset(self::$instance)) {
            self::$instance->destroy();
            self::$instance = null;
        }
    }

    /**
     * Saves the list of loaded widgets if necessary.
     */
    function __destruct()
    {
        $this->destroy();
    }

    protected function destroy()
    {
        if($this->register_widgets && count($this->loaded_widgets) > 0) {
            $sess = Session::start(APP_NAME);
            $sess->set('loaded_widgets', $this->loaded_widgets);
            $this->register_widgets = false;
        }
    }

    /**
     * Retrieves the list of loaded widgets.
     */
    function get_loaded_widgets()
    {
        if(count($this->loaded_widgets) > 0) {
            return $this->loaded_widgets;
        } else {
            return $this->loaded_widgets_old;
        }
    }
    
    function get_all_widgets()
    {
        return $this->all_widgets;
    }

    /**
     * Loads a widget and returns it.
     */
    public function load_widget($widget_name)
    {
        // Attempting to load the user's widgets in priority
        $widget_path = "";

        if(file_exists(APP_PATH . "widgets/$widget_name/$widget_name.php")) {
            $widget_path = APP_PATH . "widgets/$widget_name/$widget_name.php";
        //    $extern = false;
        }
        else {
            throw new MovimException(
                t("Requested widget '%s' doesn't exist.", $widget_name));
        }

        require_once($widget_path);
        $widget = new $widget_name();
        return $widget;
    }

    /**
     * Loads a widget and runs a particular function on it.
     *
     * @param $widget_name is the name of the widget.
     * @param $method is the function to be run.
     * @param $params is an array containing the parameters to
     *   be passed along to the method.
     * @return what the widget's method returns.
     */
    function run_widget($widget_name, $method, array $params = NULL)
    {
        if($this->register_widgets &&
           !in_array($widget_name, $this->loaded_widgets))
            $this->loaded_widgets[] = $widget_name;

        $widget = $this->load_widget($widget_name);

        if(!is_array($params))
            $params = array();
        
        $result = call_user_func_array(array($widget, $method), $params);
        // Collecting stuff generated by the widgets.
        $this->css = array_merge($this->css, $widget->loadcss());
        $this->js = array_merge($this->js, $widget->loadjs());

        return $result;
    }
    
    /**
     * We register all the events of all the widgets in the database
     * 
     * @return the registered events
     */
    function register_events() {
        $widgets = $this->get_all_widgets();

        foreach($widgets as $widget_name) {
            $widget = $this->load_widget($widget_name);
            // We save the registered events of the widget for the filter
            if(isset($widget->events))
                foreach($widget->events as $key => $value) {
                    if(array_key_exists($key, $this->registered_events)) {
                        $we = $this->registered_events[$key];
                        array_push($we, $widget_name);
                        $this->registered_events[$key] = $we;
                    } else {
                        $this->registered_events[$key] = array($widget_name);
                    }
                } 
        }
        
        return $this->registered_events;
    }

    /**
     * Calls a particular function with the given parameters on
     * all loaded widgets.
     *
     * @param $method is the method to be called on all widgets.
     * @param $params is an array of parameters passed to the method.
     */
    function iterate($method, array $params = NULL)
    {
        // We only load the interesting widgets
        if(isset($params)) {
            $fct = $params[0]['type'];
            $widgets = $this->registered_events[$fct];
        } else
            $widgets = $this->get_loaded_widgets();

        foreach($widgets as $widget)
            $this->run_widget($widget, $method, $params);
    }
    
    function iterateAll($method, array $params = NULL) {
        $widgets = $this->get_all_widgets();
        $isevent = array();
        foreach($widgets as $widget) {
            if($this->run_widget($widget, $method, $params))
                $isevent[$widget] = true;
        }
            
        return $isevent;
    }

    /**
     * Returns the list of loaded CSS.
     */
    function loadcss()
    {
        if(!is_array($this->css)) // Just being prudent
            return array();
        else
            return $this->css;
    }

    /**
     * Returns the list of loaded javascripts.
     */
    function loadjs()
    {
        if(!is_array($this->js)) // Avoids annoying errors.
            return array();
        else
            return $this->js;
    }
}

?>
