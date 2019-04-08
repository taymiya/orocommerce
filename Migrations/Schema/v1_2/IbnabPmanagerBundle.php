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
       $this->createpublicationTable($schema);

    }


    /**
     * Create ibnab_publication table
     *
     * @param Schema $schema
     */
    protected function createpublicationTable(Schema $schema)
    {
        $table = $schema->createTable('ibnab_publication');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string',['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('content', 'text', ['notnull' => false]);
        $table->addColumn('width', 'decimal', ['notnull' => false , 'scale' => 3]);
        $table->addColumn('height', 'decimal', ['notnull' => false , 'scale' => 3]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'pmanager_publication_name_idx', []); 
        $table->addIndex(['created_at'], 'pmanager_publication_created_at_idx', []);
        $table->addIndex(['updated_at'], 'pmanager_publication_updated_at_idx', []);
    }
    /**
     * Create ibnab_pmanager_template table
     *
     * @param Schema $schema
     */
    protected function createproductTemplateTable(Schema $schema)
    {
        $table = $schema->createTable('ibnab_product_template');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string',['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('content', 'text', ['notnull' => false]);
        $table->addColumn('css', 'text', ['notnull' => false]);
        $table->addColumn('layout', 'text', ['notnull' => false]);
        $table->addColumn('background', 'string', ['notnull' => false ,'length' => 30]);
        $table->addColumn('border', 'string', ['notnull' => false ,'length' => 30]);
        $table->addColumn('round', 'integer', ['notnull' => false]);
        $table->addColumn('width', 'decimal', ['notnull' => false , 'scale' => 3]);
        $table->addColumn('height', 'decimal', ['notnull' => false , 'scale' => 3]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('type', 'string', ['length' => 20]);

        
        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'pmanager_producttemplate_name_idx', []); 
        $table->addIndex(['updated_at'], 'pmanager_producttemplate_updatedat_idx', []);
        $table->addIndex(['created_at'], 'pmanager_producttemplate_createdat_idx', []);      
    }
}

