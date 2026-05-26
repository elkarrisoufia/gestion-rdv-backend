<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); $table->string('prenom'); $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); $table->string('password');
            $table->enum('role',['manager','employe','client'])->default('client');
            $table->string('telephone',20)->nullable(); $table->boolean('is_active')->default(true);
            $table->rememberToken(); $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id(); $table->morphs('tokenable'); $table->string('name');
            $table->string('token',64)->unique(); $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('employes', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('matricule',20)->unique();
            $table->string('poste',100)->default('Conseiller Clientèle');
            $table->string('agence',100)->default('Agence El Khalil El Jadida');
            $table->timestamps();
        });
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('cin',20)->unique(); $table->string('adresse')->nullable();
            $table->date('date_naissance')->nullable();
            $table->enum('type_compte',['courant','epargne','professionnel'])->default('courant');
            $table->boolean('is_vip')->default(false);
            $table->foreignId('employe_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
        Schema::create('rendez_vouses', function (Blueprint $table) {
            $table->id(); $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('employe_id')->constrained()->onDelete('cascade');
            $table->date('date_rdv'); $table->string('heure_rdv',10); $table->string('motif',255);
            $table->enum('statut',['en_attente','confirme','annule'])->default('en_attente');
            $table->timestamps();
        });
        Schema::create('emails', function (Blueprint $table) {
            $table->id(); $table->foreignId('employe_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('sujet',255); $table->text('contenu');
            $table->enum('type',['confirmation_rdv','information','commercial','notification'])->default('information');
            $table->enum('statut',['brouillon','envoye'])->default('brouillon');
            $table->timestamps();
        });
        Schema::create('statistiques', function (Blueprint $table) {
            $table->id(); $table->integer('total_rdv')->default(0);
            $table->integer('rdv_confirmes')->default(0); $table->integer('emails_envoyes')->default(0);
            $table->date('date_statistique'); $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('statistiques'); Schema::dropIfExists('emails');
        Schema::dropIfExists('rendez_vouses'); Schema::dropIfExists('clients');
        Schema::dropIfExists('employes'); Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');
    }
};
