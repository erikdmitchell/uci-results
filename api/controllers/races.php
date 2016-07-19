<?php
class Races {
    private $_params;

    public function __construct($params)
    {
        $this->_params = $params;
    }

    public function createAction()
    {
			return 'create';
    }

    public function readAction()
    {
        //read all the todo items
    }

    public function updateAction()
    {
        //update a todo item
    }

    public function deleteAction()
    {
        //delete a todo item
    }
}
?>