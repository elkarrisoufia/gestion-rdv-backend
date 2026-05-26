<?php
namespace Database\Seeders;
use App\Models\Client; use App\Models\Email; use App\Models\Employe;
use App\Models\RendezVous; use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $agence = 'Agence El Khalil El Jadida';

        // MANAGER
        $mu = User::create(['nom'=>'Benali','prenom'=>'Laila','email'=>'laila@bp.ma','password'=>Hash::make('password123'),'role'=>'manager','telephone'=>'+212661000001','is_active'=>true]);
        Employe::create(['user_id'=>$mu->id,'matricule'=>'EMP0001','poste'=>'Manager Agence','agence'=>$agence]);

        // CONSEILLERS
        $su = User::create(['nom'=>'Alaoui','prenom'=>'Sara','email'=>'sara@bp.ma','password'=>Hash::make('password123'),'role'=>'employe','telephone'=>'+212661000002','is_active'=>true]);
        $sara = Employe::create(['user_id'=>$su->id,'matricule'=>'EMP0002','poste'=>'Conseiller Clientèle','agence'=>$agence]);

        $au = User::create(['nom'=>'Oulhaj','prenom'=>'Ahmed','email'=>'ahmed@bp.ma','password'=>Hash::make('password123'),'role'=>'employe','telephone'=>'+212661000003','is_active'=>true]);
        $ahmed = Employe::create(['user_id'=>$au->id,'matricule'=>'EMP0003','poste'=>'Conseiller Clientèle','agence'=>$agence]);

        // CLIENTS
        $clientsData = [
            ['nom'=>'Boukhari','prenom'=>'Mohamed','email'=>'mb@gmail.com','tel'=>'+212662000001','cin'=>'AB123456','adresse'=>'12 Bd Mohammed V, El Jadida','type'=>'courant','vip'=>true,'emp'=>$sara->id],
            ['nom'=>'Tazi','prenom'=>'Fatima','email'=>'ftazi@gmail.com','tel'=>'+212662000002','cin'=>'CD789012','adresse'=>'45 Rue Hassan II, El Jadida','type'=>'epargne','vip'=>false,'emp'=>$sara->id],
            ['nom'=>'Mansouri','prenom'=>'Karim','email'=>'kmansouri@gmail.com','tel'=>'+212662000003','cin'=>'EF345678','adresse'=>'8 Av Bir Inzaran, El Jadida','type'=>'courant','vip'=>true,'emp'=>$ahmed->id],
            ['nom'=>'Chraibi','prenom'=>'Nadia','email'=>'nadia@gmail.com','tel'=>'+212662000004','cin'=>'GH901234','adresse'=>'23 Rue Zerktouni, El Jadida','type'=>'epargne','vip'=>false,'emp'=>$ahmed->id],
            ['nom'=>'El Idrissi','prenom'=>'Youssef','email'=>'youssef@gmail.com','tel'=>'+212662000005','cin'=>'IJ567890','adresse'=>'67 Quartier El Khalil, El Jadida','type'=>'professionnel','vip'=>true,'emp'=>$sara->id],
            ['nom'=>'Berrada','prenom'=>'Amina','email'=>'amina@gmail.com','tel'=>'+212662000006','cin'=>'KL123789','adresse'=>'15 Rue Ibn Rochd, El Jadida','type'=>'courant','vip'=>false,'emp'=>$ahmed->id],
        ];

        $clients = [];
        foreach ($clientsData as $cd) {
            $u = User::create(['nom'=>$cd['nom'],'prenom'=>$cd['prenom'],'email'=>$cd['email'],'password'=>Hash::make('password123'),'role'=>'client','telephone'=>$cd['tel'],'is_active'=>true]);
            $clients[] = Client::create(['user_id'=>$u->id,'cin'=>$cd['cin'],'adresse'=>$cd['adresse'],'type_compte'=>$cd['type'],'is_vip'=>$cd['vip'],'employe_id'=>$cd['emp']]);
        }

        // RENDEZ-VOUS (aujourd'hui + demain)
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $rdvs = [
            [$clients[0]->id,$sara->id,$today,'09:00','Ouverture de compte','confirme'],
            [$clients[1]->id,$sara->id,$today,'10:30','Crédit immobilier','en_attente'],
            [$clients[2]->id,$ahmed->id,$today,'11:00','Carte bancaire','confirme'],
            [$clients[3]->id,$ahmed->id,$today,'14:00','Virement international','en_attente'],
            [$clients[4]->id,$sara->id,$tomorrow,'09:30','Assurance habitation','confirme'],
            [$clients[5]->id,$ahmed->id,$tomorrow,'11:00','Crédit consommation','annule'],
        ];
        foreach ($rdvs as $r) {
            RendezVous::create(['client_id'=>$r[0],'employe_id'=>$r[1],'date_rdv'=>$r[2],'heure_rdv'=>$r[3],'motif'=>$r[4],'statut'=>$r[5]]);
        }

        // EMAILS
        Email::create(['employe_id'=>$sara->id,'client_id'=>$clients[0]->id,'sujet'=>'Confirmation de votre rendez-vous','contenu'=>"Cher M. Boukhari,\n\nNous confirmons votre rendez-vous à 09h00 à l'Agence El Khalil El Jadida.\n\nCordialement,\nSara Alaoui",'type'=>'confirmation_rdv','statut'=>'envoye']);
        Email::create(['employe_id'=>$ahmed->id,'client_id'=>$clients[2]->id,'sujet'=>'Votre carte bancaire Gold est disponible','contenu'=>"Cher M. Mansouri,\n\nVotre carte bancaire Gold est prête en agence.\n\nCordialement,\nAhmed Oulhaj",'type'=>'notification','statut'=>'envoye']);

        $this->command->info('');
        $this->command->info('✅ Base de données prête — Agence El Khalil El Jadida');
        $this->command->info('   Manager  : laila@bp.ma  / password123');
        $this->command->info('   Employé  : sara@bp.ma   / password123');
        $this->command->info('   Employé  : ahmed@bp.ma  / password123');
        $this->command->info('   Client   : mb@gmail.com / password123');
    }
}
