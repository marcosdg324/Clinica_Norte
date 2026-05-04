<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea 6 nuevas categorías del Catálogo con sus parámetros y requisitos,
 * y vincula los 9 exámenes que quedaron sin categoría.
 *
 * Categorías ya existentes: 1=Hemograma Completo, 2=Glucosa en Sangre, 3=Radiografía Tórax
 * Nuevas:
 *   4 = Bioquímica Metabólica   (laboratorio)
 *   5 = Función Renal           (laboratorio)
 *   6 = Función Hepática        (laboratorio)
 *   7 = Marcadores Inflamatorios(laboratorio)
 *   8 = Ecografía               (imagen)
 *   9 = Urianálisis             (laboratorio)
 */
class CatalogCompletionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── 1. Crear las 6 categorías nuevas ─────────────────────────────────

        $categories = [
            [
                'type' => 'laboratorio',
                'name' => 'Bioquímica Metabólica',
                'description' => 'Análisis bioquímicos del metabolismo de carbohidratos, lípidos y purinas. Incluye el control glucémico a largo plazo (HbA1c), el perfil lipídico completo y el ácido úrico sérico.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'laboratorio',
                'name' => 'Función Renal',
                'description' => 'Evaluación de la capacidad filtradora del riñón mediante marcadores séricos de retención nitrogenada. Permite calcular el filtrado glomerular estimado (eGFR).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'laboratorio',
                'name' => 'Función Hepática',
                'description' => 'Panel de enzimas y metabolitos para evaluar la integridad y función del hígado. Detecta hepatitis, cirrosis, obstrucción biliar y hepatotoxicidad por fármacos.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'laboratorio',
                'name' => 'Marcadores Inflamatorios',
                'description' => 'Proteínas de fase aguda sintetizadas por el hígado en respuesta a procesos inflamatorios, infecciosos o tisulares. Útiles para diagnóstico y seguimiento de infecciones y enfermedades autoinmunes.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'imagen',
                'name' => 'Ecografía',
                'description' => 'Estudio de ultrasonido en tiempo real. No utiliza radiación ionizante. Permite evaluar órganos abdominales, pélvicos y partes blandas con alta resolución.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'laboratorio',
                'name' => 'Urianálisis',
                'description' => 'Análisis físico, químico y microscópico de la orina para la evaluación del riñón, vías urinarias y detección de enfermedades sistémicas que se manifiestan en la orina.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('exam_categories')->insert($categories);

        // Obtener los IDs recién insertados por nombre
        $catIds = DB::table('exam_categories')
            ->whereIn('name', [
                'Bioquímica Metabólica', 'Función Renal', 'Función Hepática',
                'Marcadores Inflamatorios', 'Ecografía', 'Urianálisis',
            ])
            ->pluck('id', 'name');

        $bioquimica = $catIds['Bioquímica Metabólica'];
        $renal = $catIds['Función Renal'];
        $hepatica = $catIds['Función Hepática'];
        $inflamacion = $catIds['Marcadores Inflamatorios'];
        $eco = $catIds['Ecografía'];
        $orina = $catIds['Urianálisis'];

        // ── 2. Parámetros ─────────────────────────────────────────────────────

        $params = [
            // Bioquímica Metabólica
            ['exam_category_id' => $bioquimica, 'name' => 'HbA1c',             'unit' => '%',     'reference_min' => 4.80, 'reference_max' => 5.60, 'critical_min' => 3.00, 'critical_max' => 10.00, 'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $bioquimica, 'name' => 'Colesterol Total',  'unit' => 'mg/dL', 'reference_min' => 0,    'reference_max' => 199,  'critical_min' => 0,    'critical_max' => 300,   'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $bioquimica, 'name' => 'HDL Colesterol',    'unit' => 'mg/dL', 'reference_min' => 40,   'reference_max' => 60,   'critical_min' => 25,   'critical_max' => 100,   'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $bioquimica, 'name' => 'LDL Colesterol',    'unit' => 'mg/dL', 'reference_min' => 0,    'reference_max' => 99,   'critical_min' => 0,    'critical_max' => 190,   'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $bioquimica, 'name' => 'Triglicéridos',     'unit' => 'mg/dL', 'reference_min' => 0,    'reference_max' => 149,  'critical_min' => 0,    'critical_max' => 500,   'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $bioquimica, 'name' => 'Ácido Úrico',       'unit' => 'mg/dL', 'reference_min' => 3.50, 'reference_max' => 7.20, 'critical_min' => 1.00, 'critical_max' => 10.00, 'created_at' => $now, 'updated_at' => $now],

            // Función Renal
            ['exam_category_id' => $renal, 'name' => 'Urea',       'unit' => 'mg/dL', 'reference_min' => 15,   'reference_max' => 45,   'critical_min' => 5,    'critical_max' => 100,  'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $renal, 'name' => 'Creatinina', 'unit' => 'mg/dL', 'reference_min' => 0.60, 'reference_max' => 1.30, 'critical_min' => 0.30, 'critical_max' => 5.00, 'created_at' => $now, 'updated_at' => $now],

            // Función Hepática
            ['exam_category_id' => $hepatica, 'name' => 'TGO / AST',             'unit' => 'U/L',   'reference_min' => 10,   'reference_max' => 40,   'critical_min' => 0,    'critical_max' => 500,  'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $hepatica, 'name' => 'TGP / ALT',             'unit' => 'U/L',   'reference_min' => 7,    'reference_max' => 56,   'critical_min' => 0,    'critical_max' => 500,  'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $hepatica, 'name' => 'Bilirrubina Total',     'unit' => 'mg/dL', 'reference_min' => 0.20, 'reference_max' => 1.20, 'critical_min' => 0,    'critical_max' => 10.0, 'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $hepatica, 'name' => 'Bilirrubina Directa',   'unit' => 'mg/dL', 'reference_min' => 0.00, 'reference_max' => 0.30, 'critical_min' => 0,    'critical_max' => 5.00, 'created_at' => $now, 'updated_at' => $now],

            // Marcadores Inflamatorios
            ['exam_category_id' => $inflamacion, 'name' => 'Proteína C Reactiva (PCR)', 'unit' => 'mg/L', 'reference_min' => 0, 'reference_max' => 5, 'critical_min' => 0, 'critical_max' => 100, 'created_at' => $now, 'updated_at' => $now],

            // Urianálisis (parámetros cuantitativos)
            ['exam_category_id' => $orina, 'name' => 'Densidad',    'unit' => 'g/mL', 'reference_min' => 1.005, 'reference_max' => 1.030, 'critical_min' => 1.000, 'critical_max' => 1.040, 'created_at' => $now, 'updated_at' => $now],
            ['exam_category_id' => $orina, 'name' => 'pH urinario', 'unit' => 'pH',   'reference_min' => 5.0,   'reference_max' => 8.5,   'critical_min' => 4.5,   'critical_max' => 9.0,   'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('exam_parameters')->insert($params);

        // ── 3. Requisitos previos ─────────────────────────────────────────────

        $requirements = [
            // Bioquímica Metabólica
            [
                'exam_category_id' => $bioquimica,
                'exam_id' => null,
                'description' => 'Ayuno de 12 horas (para perfil lipídico)',
                'instructions' => 'Para la correcta medición del perfil lipídico (colesterol, triglicéridos) y la glucosa en ayunas, no consumir alimentos ni bebidas con calorías durante las 12 horas previas. Solo se permite agua. La HbA1c NO requiere ayuno.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Función Renal
            [
                'exam_category_id' => $renal,
                'exam_id' => null,
                'description' => 'No requiere preparación especial',
                'instructions' => 'Informar al médico sobre el consumo de medicamentos nefrotóxicos (AINEs, aminoglucósidos, contraste iodado) o suplementos de creatina que puedan alterar los resultados. Mantener hidratación normal.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Función Hepática
            [
                'exam_category_id' => $hepatica,
                'exam_id' => null,
                'description' => 'Ayuno de 4 a 6 horas recomendado',
                'instructions' => 'Evitar el consumo de alcohol al menos 24-48 horas antes. Informar sobre todos los medicamentos que esté tomando, incluyendo suplementos herbales, ya que muchos son hepatotóxicos. El ayuno es recomendado pero no estrictamente necesario.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Marcadores Inflamatorios
            [
                'exam_category_id' => $inflamacion,
                'exam_id' => null,
                'description' => 'No requiere preparación especial',
                'instructions' => 'Evitar ejercicio físico intenso las 24 horas previas, ya que puede elevar transitoriamente los valores de PCR. Informar si se ha tenido alguna infección reciente, cirugía o trauma.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Ecografía
            [
                'exam_category_id' => $eco,
                'exam_id' => null,
                'description' => 'Ayuno de 6 horas',
                'instructions' => 'No consumir alimentos ni bebidas (excepto agua) durante las 6 horas previas al examen para asegurar la adecuada distensión de la vesícula biliar. Para ecografía pélvica, mantener la vejiga llena (beber 1 litro de agua 1 hora antes sin orinar).',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Urianálisis
            [
                'exam_category_id' => $orina,
                'exam_id' => null,
                'description' => 'Recolectar primera orina de la mañana (chorro medio)',
                'instructions' => 'Lavar y secar genitales externos con agua y jabón antes de la recolección. Desechar el primer chorro de orina, recolectar en el frasco estéril la orina del chorro medio, y descartar el chorro final. No contaminar el interior del frasco. Entregar la muestra al laboratorio dentro de las 2 horas posteriores a la recolección.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('exam_requirements')->insert($requirements);

        // ── 4. Vincular los 9 exámenes sin categoría ─────────────────────────

        $links = [
            // Bioquímica Metabólica
            'Hemoglobina Glicosilada (HbA1c)' => $bioquimica,
            'Perfil Lipídico Completo' => $bioquimica,
            'Ácido Úrico Sérico' => $bioquimica,
            // Función Renal
            'Urea Sanguínea (BUN)' => $renal,
            'Creatinina Sérica' => $renal,
            // Función Hepática
            'Transaminasas TGO y TGP' => $hepatica,
            'Bilirrubinas Totales y Directas' => $hepatica,
            // Marcadores Inflamatorios
            'Proteína C Reactiva (PCR)' => $inflamacion,
            // Ecografía
            'Ecografía Abdominal' => $eco,
            // Urianálisis (existente desde el inicio)
            'Urianálisis/Orina' => $orina,
        ];

        foreach ($links as $examName => $categoryId) {
            DB::table('exams')
                ->where('name', $examName)
                ->update(['exam_category_id' => $categoryId]);
        }

        $this->command->info('✅ CatalogCompletionSeeder completado.');
        $this->command->info('   → 6 categorías nuevas creadas.');
        $this->command->info('   → 15 parámetros insertados.');
        $this->command->info('   → 6 requisitos previos insertados.');
        $this->command->info('   → 10 exámenes vinculados.');
    }
}
