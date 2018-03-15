<?php

namespace App\Core;

class Controller
{
		protected $db;

		protected $container;

		/**
		 * @var \Doctrine\ORM\EntityManager
		 */
		protected $entityManager;

    public function __construct($c)
    {
	    	$this->container = $c;
				$this->entityManager = $c->get('em');
				$this->db = $c->get('db');
    }

    public function __get($property) {
	    	if($this->container->has($property)) {
	            return $this->container->get($property);
	        }

	    	return $this->{$property};
    }
}
