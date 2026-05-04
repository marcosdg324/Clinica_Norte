<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Vincula los exámenes existentes a sus categorías del Catálogo
 * e inserta 11 exámenes nuevos para completar 15 en total.
 *
 * Categorías existentes:
 *   ID 1 → Hemograma Completo   (laboratorio)
 *   ID 2 → Glucosa en Sangre    (laboratorio)
 *   ID 3 → Radiografía de Tórax (imagen)
 */
class ExamCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Vincular exámenes existentes a sus categorías ──────────────────
        DB::table('exams')->where('id', 1)->update(['exam_category_id' => 1]); // Hemograma Completo
        DB::table('exams')->where('id', 3)->update(['exam_category_id' => 2]); // Glucosa en Ayunas
        DB::table('exams')->where('id', 2)->update(['exam_category_id' => 3]); // Radiografía de Tórax

        // ── 2. Insertar nuevos exámenes ────────────────────────────────────────
        $now = now();

        $new = [
            // --- Laboratorio: Glucosa (categoría 2) ---
            [
                'name' => 'Glucosa Postprandial (2h)',
                'exam_category_id' => 2,
                'type' => 'laboratorio',
                'price' => 95.00,
                'description' => 'Medición de glucosa en sangre 2 horas después de una carga oral de glucosa (75 g). Evalúa la respuesta insulínica y detecta diabetes e intolerancia a la glucosa.',
                'is_urgent_possible' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- Laboratorio: Hematología (categoría 1) ---
            [
                'name' => 'Velocidad de Sedimentación Globular (VSG)',
                'exam_category_id' => 1,
                'type' => 'laboratorio',
                'price' => 70.00,
                'description' => 'Mide la velocidad con que los glóbulos rojos se depositan en un tubo durante 1 hora. Marcador inespecífico de inflamación, infección o enfermedades autoinmunes.',
                'is_urgent_possible' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- Laboratorio: Bioquímica sin categoría por ahora ---
            [
                'name' => 'Hemoglobina Glicosilada (HbA1c)',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 160.00,
                'description' => 'Refleja el promedio de glucosa en sangre de los últimos 2-3 meses. Fundamental para el diagnóstico y control del tratamiento de la diabetes mellitus. Meta terapéutica: < 7% en diabéticos controlados.',
                'is_urgent_possible' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Perfil Lipídico Completo',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 195.00,
                'description' => 'Mide colesterol total, HDL, LDL y triglicéridos. Evalúa el riesgo cardiovascular y guía el tratamiento de dislipidemias. Requiere ayuno de 12 horas.',
                'is_urgent_possible' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Urea Sanguínea (BUN)',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 75.00,
                'description' => 'Mide la concentración de nitrógeno ureico en sangre. Indica la capacidad del riñón para filtrar los productos de desecho del metabolismo proteico.',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Creatinina Sérica',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 80.00,
                'description' => 'Marcador estándar de función renal. Producto del metabolismo muscular filtrado exclusivamente por el riñón. Se usa junto con urea para calcular el filtrado glomerular estimado (eGFR).',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Transaminasas TGO y TGP',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 125.00,
                'description' => 'AST (TGO) y ALT (TGP): enzimas hepáticas cuya elevación indica daño en células del hígado. Esenciales para el diagnóstico de hepatitis, cirrosis y hepatotoxicidad por medicamentos.',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Proteína C Reactiva (PCR)',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 115.00,
                'description' => 'Proteína de fase aguda sintetizada por el hígado ante procesos inflamatorios o infecciosos. Útil para monitoreo de infecciones bacterianas, enfermedades inflamatorias y riesgo cardiovascular (PCR ultrasensible).',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Ácido Úrico Sérico',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 75.00,
                'description' => 'Producto final del metabolismo de las purinas. Niveles elevados (hiperuricemia) se asocian a gota, nefrolitiasis y síndrome metabólico.',
                'is_urgent_possible' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bilirrubinas Totales y Directas',
                'exam_category_id' => null,
                'type' => 'laboratorio',
                'price' => 100.00,
                'description' => 'Evalúa la función hepática y biliar. La bilirrubina directa (conjugada) indica obstrucción biliar o enfermedad hepática; la indirecta (no conjugada) sugiere hemólisis o síndrome de Gilbert.',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- Imagen sin categoría por ahora ---
            [
                'name' => 'Ecografía Abdominal',
                'exam_category_id' => null,
                'type' => 'imagen',
                'price' => 380.00,
                'description' => 'Estudio ecográfico de hígado, vesícula, páncreas, riñones, bazo y grandes vasos abdominales. No utiliza radiación ionizante. Requiere ayuno de 6 horas para mejor visualización de vesícula.',
                'is_urgent_possible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('exams')->insert($new);

        $this->command->info('✅ ExamCatalogSeeder: vínculos actualizados y 11 exámenes nuevos insertados.');
        $this->command->table(
            ['ID', 'Nombre', 'Tipo', 'Precio', 'Categoría'],
            DB::table('exams as e')
                ->leftJoin('exam_categories as c', 'c.id', '=', 'e.exam_category_id')
                ->select('e.id', 'e.name', 'e.type', 'e.price', DB::raw("COALESCE(c.name, '—') as category"))
                ->orderBy('e.id')
                ->get()
                ->toArray()
        );
    }
}
