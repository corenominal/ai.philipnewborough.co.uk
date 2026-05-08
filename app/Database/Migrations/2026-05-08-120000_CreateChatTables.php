<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'       => ['type' => 'VARCHAR', 'constraint' => 36],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'New Chat'],
            'model'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'pinned'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('uuid');
        $this->forge->createTable('chat_sessions');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role'       => ['type' => 'ENUM', 'constraint' => ['user', 'assistant']],
            'content'    => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('session_id');
        $this->forge->createTable('chat_messages');
    }

    public function down()
    {
        $this->forge->dropTable('chat_messages');
        $this->forge->dropTable('chat_sessions');
    }
}
