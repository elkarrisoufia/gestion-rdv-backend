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
        $mu = User::firstOrCreate(['email'=>'laila@bp.ma'],['nom'=>'Benali','prenom'=>'Laila','password'=>Hash::make('password123'),'role'=>'manager','telephone'=>'+212661000001','is_active'=>true]);
        $manager = Employe::firstOrCreate(['user_id'=>$mu->id],['matricule'=>'EMP0001','poste'=>'Manager Agence','agence'=>$agence]);

        // CONSEILLERS
        $su = User::firstOrCreate(['email'=>'sara@bp.ma'],['nom'=>'Alaoui','prenom'=>'Sara','password'=>Hash::make('password123'),'role'=>'employe','telephone'=>'+212661000002','is_active'=>true]);
        $sara = Employe::firstOrCreate(['user_id'=>$su->id],['matricule'=>'EMP0002','poste'=>'Conseiller Clientèle','agence'=>$agence]);

        $au = User::firstOrCreate(['email'=>'ahmed@bp.ma'],['nom'=>'Oulhaj','prenom'=>'Ahmed','password'=>Hash::make('password123'),'role'=>'employe','telephone'=>'+212661000003','is_active'=>true]);
        $ahmed = Employe::firstOrCreate(['user_id'=>$au->id],['matricule'=>'EMP0003','poste'=>'Conseiller Clientèle','agence'=>$agence]);

        // CLIENTS
        $clientsData = [
            ['nom'=>'Boukhari','prenom'=>'Mohamed','email'=>'mb@gmail.com','tel'=>'+212662000001','cin'=>'AB123456','adresse'=>'12 Bd Mohammed V, El Jadida','type'=>'courant','vip'=>true,'emp'=>$sara->id],
            ['nom'=>'Tazi','prenom'=>'Fatima','email'=>'ftazi@gmail.com','tel'=>'+212662000002','cin'=>'CD789012','adresse'=>'45 Rue Hassan II, El Jadida','type'=>'epargne','vip'=>false,'emp'=>$sara->id],
            ['nom'=>'Mansouri','prenom'=>'Karim','email'=>'kmansouri@gmail.com','tel'=>'+212662000003','cin'=>'EF345678','adresse'=>'8 Av Bir Inzaran, El Jadida','type'=>'courant','vip'=>true,'emp'=>$ahmed->id],
            ['nom'=>'Chraibi','prenom'=>'Nadia','email'=>'nadia@gmail.com','tel'=>'+212662000004','cin'=>'GH901234','adresse'=>'23 Rue Zerktouni, El Jadida','type'=>'epargne','vip'=>false,'emp'=>$ahmed->id],
            ['nom'=>'El Idrissi','prenom'=>'Youssef','email'=>'youssef@gmail.com','tel'=>'+212662000005','cin'=>'IJ567890','adresse'=>'67 Quartier El Khalil, El Jadida','type'=>'professionnel','vip'=>true,'emp'=>$sara->id],
            ['nom'=>'Berrada','prenom'=>'Amina','email'=>'amina@gmail.com','tel'=>'+212662000006','cin'=>'KL123789','adresse'=>'15 Rue Ibn Rochd, El Jadida','type'=>'courant','vip'=>false,'emp'=>$ahmed->id],
        ];

        foreach ($clientsData as $cd) {
            $u = User::firstOrCreate(['email'=>$cd['email']],['nom'=>$cd['nom'],'prenom'=>$cd['prenom'],'password'=>Hash::make('password123'),'role'=>'client','telephone'=>$cd['tel'],'is_active'=>true]);
            Client::firstOrCreate(['cin'=>$cd['cin']],['user_id'=>$u->id,'adresse'=>$cd['adresse'],'type_compte'=>$cd['type'],'is_vip'=>$cd['vip'],'employe_id'=>$cd['emp']]);
        }

        $this->command->info('✅ Base de données prête — Agence El Khalil El Jadida');
    }
}