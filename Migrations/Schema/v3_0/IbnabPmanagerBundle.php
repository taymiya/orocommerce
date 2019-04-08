<?php

namespace Ibnab\Bundle\PmanagerBundle\Migrations\Schema\v3_0;

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
        $this->createPDFTemplateLogsTable($schema);
    }


    /**
     * Create ibnab_pmanager_logs table
     *
     * @param Schema $schema
     */
    protected function createPDFTemplateLogsTable(Schema $schema)
    {
        $table = $schema->createTable('ibnab_pmanager_logs');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('entitytargetId', 'string', ['length' => 255,'notnull' => false]);
        $table->addColumn('entityName', 'string', ['length' => 255]);
        $table->addColumn('side', 'string', ['length' => 45]);
        $table->addColumn('type', 'string', ['length' => 10]);
        $table->addColumn('realname', 'text', ['length' => 10]);
        $table->addColumn('filename', 'text', ['notnull' => false]);
        $table->addColumn('filepath', 'text', ['notnull' => false]);
        $table->addColumn('templateId', 'integer', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('user_owner_id', 'integer', []);
        $table->addColumn('organization_id', 'integer', []);


        $table->setPrimaryKey(['id']);
        $table->addIndex(['entityName'], 'pmanager_pdftemplate_logs_entity_name_idx', []);
        $table->addIndex(['user_owner_id'], 'IDX_ibnablogsEE2DB9449EB185F9', []);
        $table->addIndex(['organization_id'], 'IDX_ibnablogsEE2DB94432C8A3DE', []);
        $table->addIndex(['createdAt'], 'pmanager_pdftemplate_logs_created_at_idx', []);
        $table->addIndex(['side'], 'pmanager_pdftemplate_logs_side_idx', []);
        $table->addIndex(['realname'], 'pmanager_pdftemplate_logs_realname_idx', []);
        $table = $schema->getTable('ibnab_pmanager_logs');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            []
        );      
    }
}

