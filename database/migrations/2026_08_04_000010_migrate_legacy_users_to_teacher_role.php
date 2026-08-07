<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('rol', 'usuario')->update(['rol' => 'profesor']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('profesor')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('usuario')->change();
        });
    }
};
