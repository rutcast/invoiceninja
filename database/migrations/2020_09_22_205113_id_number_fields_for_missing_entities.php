<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IdNumberFieldsForMissingEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('number')->nullable();
        });
    
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('number')->nullable();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->text('vendor_hash')->nullable();
            $table->text('public_notes')->nullable();
        });

        Schema::table('vendor_contacts', function (Blueprint $table) {
            $table->boolean('send_email')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}