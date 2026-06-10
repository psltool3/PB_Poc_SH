<?php

class StoragePoint{
  public $Id;
  public $Name;

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * Set the value of Id
     *
     * @param mixed $Id
     *
     * @return self
     */
    public function setId($Id)
    {
        $this->Id = $Id;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * Set the value of name
     *
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->Name = $name;

        return $this;
    }


    function insert(StoragePoint $StoragePoint){

        return "INSERT INTO storage_point(id,name) VALUES ('".$StoragePoint->getId()."','".$StoragePoint->getName()."')";

    }
	
	function check(StoragePoint $StoragePoint){

        return "SELECT * FROM storage_point WHERE name='".$StoragePoint->getName()."'";

    }

    function delete(StoragePoint $StoragePoint){

        return "DELETE FROM storage_point WHERE id='".$StoragePoint->getId()."'";

    }
	
	function logname(StoragePoint $StoragePoint){

        return "SELECT name FROM storage_point WHERE id='".$StoragePoint->getId()."'";

    }

    function update(StoragePoint $StoragePoint){

      return  "UPDATE storage_point SET name='".$StoragePoint->getName()."' WHERE id = '".$StoragePoint->getId()."'";

    }

    function checkInsert(StoragePoint $StoragePoint){

        return "SELECT * FROM storage_point WHERE LOWER(id)=LOWER('".$StoragePoint->getId()."')";

    }

    function checkEdit(StoragePoint $StoragePoint){

        return "SELECT * FROM storage_point WHERE LOWER(id)=LOWER('".$StoragePoint->getId()."')";

    }

    function updateEdit(StoragePoint $StoragePoint){

        return "UPDATE storage_point SET name='".$StoragePoint->getName()."' WHERE id = '".$StoragePoint->getId()."'";

    }

}

 ?>
