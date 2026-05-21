<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentUsersSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'IT' => [
                ['Maria Santos',      'maria.santos@ecticketing.com'],
                ['Juan Reyes',        'juan.reyes@ecticketing.com'],
                ['Ana Dela Cruz',     'ana.delacruz@ecticketing.com'],
                ['Pedro Bautista',    'pedro.bautista@ecticketing.com'],
                ['Rosa Villanueva',   'rosa.villanueva@ecticketing.com'],
                ['Carlo Mendoza',     'carlo.mendoza@ecticketing.com'],
                ['Liza Aquino',       'liza.aquino@ecticketing.com'],
                ['Mark Ramos',        'mark.ramos@ecticketing.com'],
                ['Jenny Castillo',    'jenny.castillo@ecticketing.com'],
                ['Rico Garcia',       'rico.garcia@ecticketing.com'],
            ],
            'HR' => [
                ['Elena Flores',      'elena.flores@ecticketing.com'],
                ['Ryan Gonzales',     'ryan.gonzales@ecticketing.com'],
                ['Claire Morales',    'claire.morales@ecticketing.com'],
                ['Dennis Torres',     'dennis.torres@ecticketing.com'],
                ['Maribel Cruz',      'maribel.cruz@ecticketing.com'],
                ['Allan Diaz',        'allan.diaz@ecticketing.com'],
                ['Janna Reyes',       'janna.reyes@ecticketing.com'],
                ['Roberto Lim',       'roberto.lim@ecticketing.com'],
                ['Patricia Chan',     'patricia.chan@ecticketing.com'],
                ['Edwin Tan',         'edwin.tan@ecticketing.com'],
            ],
            'Finance' => [
                ['Sophia Ng',         'sophia.ng@ecticketing.com'],
                ['Kevin Ong',         'kevin.ong@ecticketing.com'],
                ['Melissa Go',        'melissa.go@ecticketing.com'],
                ['Roland Chua',       'roland.chua@ecticketing.com'],
                ['Vanessa Sy',        'vanessa.sy@ecticketing.com'],
                ['Patrick Ko',        'patrick.ko@ecticketing.com'],
                ['Rachel Yu',         'rachel.yu@ecticketing.com'],
                ['Bryan Lim',         'bryan.lim@ecticketing.com'],
                ['Diana Tan',         'diana.tan@ecticketing.com'],
                ['Jeremy Wong',       'jeremy.wong@ecticketing.com'],
            ],
            'OPIC' => [
                ['Andrea Soriano',    'andrea.soriano@ecticketing.com'],
                ['Francis Manalo',    'francis.manalo@ecticketing.com'],
                ['Kristine Tolentino','kristine.tolentino@ecticketing.com'],
                ['Paolo Navarro',     'paolo.navarro@ecticketing.com'],
                ['Gina Espiritu',     'gina.espiritu@ecticketing.com'],
                ['Renato Ocampo',     'renato.ocampo@ecticketing.com'],
                ['Clarissa Padua',    'clarissa.padua@ecticketing.com'],
                ['Jomar Salcedo',     'jomar.salcedo@ecticketing.com'],
                ['Maricel Vega',      'maricel.vega@ecticketing.com'],
                ['Angelo Briones',    'angelo.briones@ecticketing.com'],
            ],
            'Dispatch' => [
                ['Ramon Cabrera',     'ramon.cabrera@ecticketing.com'],
                ['Teresita Magno',    'teresita.magno@ecticketing.com'],
                ['Eduardo Herrera',   'eduardo.herrera@ecticketing.com'],
                ['Lorena Valdez',     'lorena.valdez@ecticketing.com'],
                ['Fernando Aguilar',  'fernando.aguilar@ecticketing.com'],
                ['Cristina Salas',    'cristina.salas@ecticketing.com'],
                ['Danilo Rojas',      'danilo.rojas@ecticketing.com'],
                ['Natividad Perez',   'natividad.perez@ecticketing.com'],
                ['Alfredo Rios',      'alfredo.rios@ecticketing.com'],
                ['Corazon Santos',    'corazon.santos@ecticketing.com'],
            ],
            'Asset/Admin' => [
                ['Rowena Dela Torre', 'rowena.delatorre@ecticketing.com'],
                ['Efren Molina',      'efren.molina@ecticketing.com'],
                ['Marilou Agustin',   'marilou.agustin@ecticketing.com'],
                ['Rodrigo Sevilla',   'rodrigo.sevilla@ecticketing.com'],
                ['Cecilia Nunez',     'cecilia.nunez@ecticketing.com'],
                ['Alberto Guerrero',  'alberto.guerrero@ecticketing.com'],
                ['Erlinda Mendez',    'erlinda.mendez@ecticketing.com'],
                ['Ernesto Sandoval',  'ernesto.sandoval@ecticketing.com'],
                ['Gloria Castro',     'gloria.castro@ecticketing.com'],
                ['Isagani Ramirez',   'isagani.ramirez@ecticketing.com'],
            ],
            'Marketing' => [
                ['Katrina Villafuerte','katrina.villafuerte@ecticketing.com'],
                ['Jerome Lacson',     'jerome.lacson@ecticketing.com'],
                ['Pamela Hernandez',  'pamela.hernandez@ecticketing.com'],
                ['Vincent Castillo',  'vincent.castillo@ecticketing.com'],
                ['Aileen Guevarra',   'aileen.guevarra@ecticketing.com'],
                ['Christopher Ruiz',  'christopher.ruiz@ecticketing.com'],
                ['Diana Espinoza',    'diana.espinoza@ecticketing.com'],
                ['Leo Fernandez',     'leo.fernandez@ecticketing.com'],
                ['Sofia Dela Pena',   'sofia.delapena@ecticketing.com'],
                ['Adrian Macapagal',  'adrian.macapagal@ecticketing.com'],
            ],
            'RSO' => [
                ['Domingo Andrade',   'domingo.andrade@ecticketing.com'],
                ['Nilda Esguerra',    'nilda.esguerra@ecticketing.com'],
                ['Salvador Corpuz',   'salvador.corpuz@ecticketing.com'],
                ['Rosario Tejada',    'rosario.tejada@ecticketing.com'],
                ['Conrado Pascual',   'conrado.pascual@ecticketing.com'],
                ['Florencia Estrada', 'florencia.estrada@ecticketing.com'],
                ['Leandro Paredes',   'leandro.paredes@ecticketing.com'],
                ['Beatriz Medina',    'beatriz.medina@ecticketing.com'],
                ['Augusto Reyes',     'augusto.reyes@ecticketing.com'],
                ['Concepcion Hidalgo','concepcion.hidalgo@ecticketing.com'],
            ],
            'Store' => [
                ['Arsenio Dalisay',   'arsenio.dalisay@ecticketing.com'],
                ['Ligaya Serrano',    'ligaya.serrano@ecticketing.com'],
                ['Benigno Buenaventura','benigno.buenaventura@ecticketing.com'],
                ['Luzviminda Tria',   'luzviminda.tria@ecticketing.com'],
                ['Simeon Carino',     'simeon.carino@ecticketing.com'],
                ['Rosalinda Estrella','rosalinda.estrella@ecticketing.com'],
                ['Enrique Tuazon',    'enrique.tuazon@ecticketing.com'],
                ['Leticia Pineda',    'leticia.pineda@ecticketing.com'],
                ['Porfirio Tagaytay', 'porfirio.tagaytay@ecticketing.com'],
                ['Milagros Aguinaldo','milagros.aguinaldo@ecticketing.com'],
            ],
        ];

        $password = Hash::make('password');

        foreach ($departments as $dept => $users) {
            foreach ($users as [$name, $email]) {
                User::firstOrCreate(
                    ['email' => $email],
                    ['name' => $name, 'department' => $dept, 'password' => $password]
                );
            }
        }
    }
}
