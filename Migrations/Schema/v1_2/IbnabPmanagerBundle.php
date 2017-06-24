<?php

namespace Ibnab\Bundle\PmanagerBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class IbnabPmanagerBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createproductTemplateTable($schema);

    }


    /**
     * Create ibnab_pmanager_template table
     *
     * @param Schema $schema
     */
    protected function createproductTemplateTable(Schema $schema)
    {
        $table = $schema->createTable('ibnab_pmanager_template');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string',['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('content', 'text', ['notnull' => false]);
        $table->addColumn('background', 'string', ['notnull' => false ,'length' => 30]);
        $table->addColumn('border', 'string', ['notnull' => false ,'length' => 30]);
        $table->addColumn('round', 'integer', ['notnull' => false]);
        $table->addColumn('width', 'double', ['notnull' => false , 'scale' => 3]);
        $table->addColumn('height', 'double', ['notnull' => false , 'scale' => 3]);


        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name', 'entityName'], 'UQ_NAME');
        $table->addIndex(['name'], 'pmanager_producttemplate_name_idx', []);     
    }
}

