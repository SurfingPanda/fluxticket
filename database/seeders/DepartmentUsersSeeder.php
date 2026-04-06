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
                ['Maria Santos',      'maria.santos@fluxtickets.com'],
                ['Juan Reyes',        'juan.reyes@fluxtickets.com'],
                ['Ana Dela Cruz',     'ana.delacruz@fluxtickets.com'],
                ['Pedro Bautista',    'pedro.bautista@fluxtickets.com'],
                ['Rosa Villanueva',   'rosa.villanueva@fluxtickets.com'],
                ['Carlo Mendoza',     'carlo.mendoza@fluxtickets.com'],
                ['Liza Aquino',       'liza.aquino@fluxtickets.com'],
                ['Mark Ramos',        'mark.ramos@fluxtickets.com'],
                ['Jenny Castillo',    'jenny.castillo@fluxtickets.com'],
                ['Rico Garcia',       'rico.garcia@fluxtickets.com'],
            ],
            'HR' => [
                ['Elena Flores',      'elena.flores@fluxtickets.com'],
                ['Ryan Gonzales',     'ryan.gonzales@fluxtickets.com'],
                ['Claire Morales',    'claire.morales@fluxtickets.com'],
                ['Dennis Torres',     'dennis.torres@fluxtickets.com'],
                ['Maribel Cruz',      'maribel.cruz@fluxtickets.com'],
                ['Allan Diaz',        'allan.diaz@fluxtickets.com'],
                ['Janna Reyes',       'janna.reyes@fluxtickets.com'],
                ['Roberto Lim',       'roberto.lim@fluxtickets.com'],
                ['Patricia Chan',     'patricia.chan@fluxtickets.com'],
                ['Edwin Tan',         'edwin.tan@fluxtickets.com'],
            ],
            'Finance' => [
                ['Sophia Ng',         'sophia.ng@fluxtickets.com'],
                ['Kevin Ong',         'kevin.ong@fluxtickets.com'],
                ['Melissa Go',        'melissa.go@fluxtickets.com'],
                ['Roland Chua',       'roland.chua@fluxtickets.com'],
                ['Vanessa Sy',        'vanessa.sy@fluxtickets.com'],
                ['Patrick Ko',        'patrick.ko@fluxtickets.com'],
                ['Rachel Yu',         'rachel.yu@fluxtickets.com'],
                ['Bryan Lim',         'bryan.lim@fluxtickets.com'],
                ['Diana Tan',         'diana.tan@fluxtickets.com'],
                ['Jeremy Wong',       'jeremy.wong@fluxtickets.com'],
            ],
            'OPIC' => [
                ['Andrea Soriano',    'andrea.soriano@fluxtickets.com'],
                ['Francis Manalo',    'francis.manalo@fluxtickets.com'],
                ['Kristine Tolentino','kristine.tolentino@fluxtickets.com'],
                ['Paolo Navarro',     'paolo.navarro@fluxtickets.com'],
                ['Gina Espiritu',     'gina.espiritu@fluxtickets.com'],
                ['Renato Ocampo',     'renato.ocampo@fluxtickets.com'],
                ['Clarissa Padua',    'clarissa.padua@fluxtickets.com'],
                ['Jomar Salcedo',     'jomar.salcedo@fluxtickets.com'],
                ['Maricel Vega',      'maricel.vega@fluxtickets.com'],
                ['Angelo Briones',    'angelo.briones@fluxtickets.com'],
            ],
            'Dispatch' => [
                ['Ramon Cabrera',     'ramon.cabrera@fluxtickets.com'],
                ['Teresita Magno',    'teresita.magno@fluxtickets.com'],
                ['Eduardo Herrera',   'eduardo.herrera@fluxtickets.com'],
                ['Lorena Valdez',     'lorena.valdez@fluxtickets.com'],
                ['Fernando Aguilar',  'fernando.aguilar@fluxtickets.com'],
                ['Cristina Salas',    'cristina.salas@fluxtickets.com'],
                ['Danilo Rojas',      'danilo.rojas@fluxtickets.com'],
                ['Natividad Perez',   'natividad.perez@fluxtickets.com'],
                ['Alfredo Rios',      'alfredo.rios@fluxtickets.com'],
                ['Corazon Santos',    'corazon.santos@fluxtickets.com'],
            ],
            'Asset/Admin' => [
                ['Rowena Dela Torre', 'rowena.delatorre@fluxtickets.com'],
                ['Efren Molina',      'efren.molina@fluxtickets.com'],
                ['Marilou Agustin',   'marilou.agustin@fluxtickets.com'],
                ['Rodrigo Sevilla',   'rodrigo.sevilla@fluxtickets.com'],
                ['Cecilia Nunez',     'cecilia.nunez@fluxtickets.com'],
                ['Alberto Guerrero',  'alberto.guerrero@fluxtickets.com'],
                ['Erlinda Mendez',    'erlinda.mendez@fluxtickets.com'],
                ['Ernesto Sandoval',  'ernesto.sandoval@fluxtickets.com'],
                ['Gloria Castro',     'gloria.castro@fluxtickets.com'],
                ['Isagani Ramirez',   'isagani.ramirez@fluxtickets.com'],
            ],
            'Marketing' => [
                ['Katrina Villafuerte','katrina.villafuerte@fluxtickets.com'],
                ['Jerome Lacson',     'jerome.lacson@fluxtickets.com'],
                ['Pamela Hernandez',  'pamela.hernandez@fluxtickets.com'],
                ['Vincent Castillo',  'vincent.castillo@fluxtickets.com'],
                ['Aileen Guevarra',   'aileen.guevarra@fluxtickets.com'],
                ['Christopher Ruiz',  'christopher.ruiz@fluxtickets.com'],
                ['Diana Espinoza',    'diana.espinoza@fluxtickets.com'],
                ['Leo Fernandez',     'leo.fernandez@fluxtickets.com'],
                ['Sofia Dela Pena',   'sofia.delapena@fluxtickets.com'],
                ['Adrian Macapagal',  'adrian.macapagal@fluxtickets.com'],
            ],
            'RSO' => [
                ['Domingo Andrade',   'domingo.andrade@fluxtickets.com'],
                ['Nilda Esguerra',    'nilda.esguerra@fluxtickets.com'],
                ['Salvador Corpuz',   'salvador.corpuz@fluxtickets.com'],
                ['Rosario Tejada',    'rosario.tejada@fluxtickets.com'],
                ['Conrado Pascual',   'conrado.pascual@fluxtickets.com'],
                ['Florencia Estrada', 'florencia.estrada@fluxtickets.com'],
                ['Leandro Paredes',   'leandro.paredes@fluxtickets.com'],
                ['Beatriz Medina',    'beatriz.medina@fluxtickets.com'],
                ['Augusto Reyes',     'augusto.reyes@fluxtickets.com'],
                ['Concepcion Hidalgo','concepcion.hidalgo@fluxtickets.com'],
            ],
            'Store' => [
                ['Arsenio Dalisay',   'arsenio.dalisay@fluxtickets.com'],
                ['Ligaya Serrano',    'ligaya.serrano@fluxtickets.com'],
                ['Benigno Buenaventura','benigno.buenaventura@fluxtickets.com'],
                ['Luzviminda Tria',   'luzviminda.tria@fluxtickets.com'],
                ['Simeon Carino',     'simeon.carino@fluxtickets.com'],
                ['Rosalinda Estrella','rosalinda.estrella@fluxtickets.com'],
                ['Enrique Tuazon',    'enrique.tuazon@fluxtickets.com'],
                ['Leticia Pineda',    'leticia.pineda@fluxtickets.com'],
                ['Porfirio Tagaytay', 'porfirio.tagaytay@fluxtickets.com'],
                ['Milagros Aguinaldo','milagros.aguinaldo@fluxtickets.com'],
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
