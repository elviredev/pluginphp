<?php

namespace Migration;

/**
 * @desc Description de la table `users`
 * Users Migration
 */
class Users extends Migration
{
  public function up()
  {
    $this->addColumn('id int unsigned auto_increment');
    $this->addColumn('column1 varchar(100) default NULL');
    $this->addColumn('column2 varchar(100) default NULL');
    $this->addColumn('deleted tinyint(1) default 0');
    $this->addColumn('date_created datetime default NULL');
    $this->addColumn('date_updated datetime default NULL');
    $this->addColumn('date_deleted datetime default NULL');

    $this->addPrimaryKey('id');
    $this->addKey('deleted');
    $this->addKey('date_created');

    $this->createTable('users');
  }

  public function down()
  {
    $this->drop('users');
  }
}