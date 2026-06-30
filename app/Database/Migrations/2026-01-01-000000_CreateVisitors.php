<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitors extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'   => ['type' => 'INT', 'constraint' => 9, 'unsigned' => true, 'auto_increment' => true],
            'note' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('visitors');
    }

    public function down()
    {
        $this->forge->dropTable('visitors');
    }
}
