<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use DB;
use Hash;

class ConsortiumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccplus:addconsortium';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup a new consortium for the CC+ application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // Get basic info for the new consortium
        $conso_data['name'] = $this->ask('New consortium name?');
        $conso_data['email'] = $this->ask('Primary email for the cosortium?');
        $conso_data['ccp_key'] = $this->ask('Provide a unique database key for the consortium
  (default creates a random string) []');
        if ($conso_data['ccp_key'] == "") {
            $conso_data['ccp_key'] = uniqid();
        }
     // Make sure the new database to be created doesn't already exist
        $conso_db = "ccplus_" . $conso_data['ccp_key'];
        $count = DB::table('information_schema.tables')->where("TABLE_SCHEMA", "=", $conso_db)->count();
        if ($count > 0) {
            $this->error('Another database named ' . $conso_db . ' already exists!');
            exit;
        }
        $_active = $this->ask('Make it active (Y/N) [Y]?');
        if ($_active == "") {
            $_active = "Y";
        }
        $conso_data['is_active'] = (strtoupper($_active) == 'Y') ? 1 : 0;

      // Create the new database as a copy of the con_template
      // ----------------------------------------------------------------
      // New database uses template host, charset, and collation settings
        $global_db   = \Config::get('database.connections.globaldb.database');
        $template_db = \Config::get('database.connections.con_template.database');
        $_host = \Config::get('database.connections.con_template.host');
        $_cset = \Config::get('database.connections.con_template.charset');
        $_coll = \Config::get('database.connections.con_template.collation');

      // Setup usernames/passwords for accessing the database (used by grants below)
        $admin_user = \Config::get('database.connections.globaldb.username');
        $admin_pass = \Config::get('database.connections.globaldb.password');
        $conso_user = \Config::get('database.connections.consodb.username');
        $conso_pass = \Config::get('database.connections.consodb.password');

      // Create the database
        DB::statement("CREATE DATABASE `" . $conso_db . "` CHARACTER SET " . $_cset . ' COLLATE ' . $_coll);

      // Set configuration to use the new database as consodb
        \Config::set('database.connections.consodb', [
                   'driver'    => 'mysql',
                   'host'      => $_host,
                   'database'  => $conso_db,
                   'username'  => $admin_user,
                   'password'  => $admin_pass,
                   'charset'   => $_cset,
                   'collation' => $_coll,
                   'strict'    => false,
                   'options'   => [ \PDO::ATTR_EMULATE_PREPARES => true ]
        ]);
        DB::reconnect();

      // Run con_template migrations on the new database
        $exitCode = $this->call('migrate:fresh', [
            '--force' => true,
            '--path' => '/database/migrations/con_template',
            '--database' => 'consodb',
        ]);
        $this->line('<fg=cyan>New database migration completed with status: ' . $exitCode);

      // Run con_template migrations on the new database
        $exitCode = $this->call('db:seed');
        $this->line('<fg=cyan>Initial database seeding completed with status: ' . $exitCode);

      // Grants for consortia admin and user access to the MySQL database
        $_grant_Adm  = "GRANT ALL on `" . $conso_db . "`.* TO '" . $admin_user . "'@'" . $_host .
                       "' identified by '" . $admin_pass . "'";
        DB::statement($_grant_Adm);
        $_grant_Usr = "GRANT SELECT on `" . $conso_db . "`.* TO '" . $conso_user . "'@'" . $_host .
                      "' identified by '" . $conso_pass . "'";
        DB::statement($_grant_Usr);
        $_grant_Usr = "GRANT UPDATE on `" . $conso_db . "`.users TO '" . $conso_user . "'@'" . $_host .
                      "' identified by '" . $conso_pass . "'";
        DB::statement($_grant_Usr);

      // Update global consortia database table
        DB::table($global_db . '.consortia')->insert($conso_data);
        $this->line('<fg=cyan>Consortia added to global database.');

      // Create the Administrator account in the users table
        $this->info('The initial Administrator acccount for a new consortium is always created with');
        $this->info('an email address set to "Administrator".');
        $_pass = $this->secret('Enter a password for this Administrator account?');
        DB::table($conso_db . ".users")->insert([
        ['name' => 'CC-Plus Administrator',
         'password' => Hash::make($_pass),
         'email' => 'Administrator',
         'inst_id' => 1,
         'is_active' => 1]
        ]);

      // Set Admin role for 'Administrator'
        DB::table($conso_db . ".role_user")->insert(['role_id' =>  99, 'user_id' => 1]);

        $this->line('<fg=cyan>New consortium : ' . $conso_data['name'] . ' Successfully Created.');
    }
}
