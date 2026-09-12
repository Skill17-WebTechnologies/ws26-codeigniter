<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitors extends Migration
{
    /**
     * Every project a competitor creates shares ONE MySQL database, so the table
     * name is prefixed to keep it from colliding with their other applications.
     * Rename the prefix per project; do not drop it.
     */
    private const TABLE = 'ci_visitors';

    public function up()
    {
        $this->forge->addField([
            'id'   => ['type' => 'INT', 'constraint' => 9, 'unsigned' => true, 'auto_increment' => true],
            'note' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable(self::TABLE);
    }

    public function down()
    {
        $this->forge->dropTable(self::TABLE);
    }
}
