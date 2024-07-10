<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('status')->default(1)->after('company_website'); //'jobs' table ekta 'status' column ekk add kirima
            $table->integer('isFeatured')->after('status')->default(0); //'jobs' table ekta 'isFeatured' column ekk add kirima
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('isFeatured');
        });
    }
};
