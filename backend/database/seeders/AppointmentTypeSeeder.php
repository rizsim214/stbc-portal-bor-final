<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointmentTypes = [
            [
                'name' => 'Initial Consultation',
                'description' => 'General consultation for first-visit concerns, symptoms, vitals, and care planning.',
            ],
            [
                'name' => 'Medical Check-up',
                'description' => 'General medical assessment for routine checkups and common health concerns.',
            ],
            [
                'name' => 'Clinical Laboratory Tests',
                'description' => 'General laboratory appointment for blood, urine, stool, and infection screening.',
            ],
            [
                'name' => '2D-Echo',
                'description' => 'Cardiac ultrasound to assess heart structure and pumping function.',
            ],
            [
                'name' => 'ECG',
                'description' => 'Electrocardiogram for heart rhythm and electrical activity screening.',
            ],
            // [
            //     -> Currently Not Applicable due to accreditation certificate expired
            //     -> Remove When certificate is renewed
            //     'name' => 'Drug Testing',
            //     'description' => 'Substance screening for employment, compliance, or medical clearance needs.',
            // ],
            [
                'name' => 'Phlebotomy / Blood Extraction',
                'description' => 'Blood collection appointment for requested laboratory workups.',
            ],
            [
                'name' => 'Chemistry Tests',
                'description' => 'Laboratory chemistry tests for blood sugar, kidney, liver, and electrolyte checks.',
            ],
            [
                'name' => 'Hematology Tests',
                'description' => 'Blood cell, clotting, and blood typing laboratory tests.',
            ],
            [
                'name' => 'Clinical Microscopy',
                'description' => 'Urine and stool examinations for infection, parasites, and digestive concerns.',
            ],
            [
                'name' => 'Serology Tests',
                'description' => 'Infection screening and immune response marker testing.',
            ],
            [
                'name' => 'Special Examinations',
                'description' => 'Specialized tests ordered for deeper assessment of specific conditions.',
            ],
            ['name' => 'FBS/RBS', 'description' => 'Blood sugar testing for diabetes screening and monitoring.'],
            ['name' => '75g OGTT', 'description' => 'Glucose tolerance testing after a 75g oral glucose load.'],
            ['name' => 'BUA', 'description' => 'Blood uric acid test for gout and kidney-related concerns.'],
            ['name' => 'Creatinine', 'description' => 'Kidney function test that measures filtration performance.'],
            ['name' => 'BUN', 'description' => 'Blood urea nitrogen test for kidney function and hydration checks.'],
            ['name' => 'Lipid Profile', 'description' => 'Cholesterol and triglyceride panel for cardiovascular risk screening.'],
            ['name' => 'SGPT', 'description' => 'ALT liver enzyme test used to assess liver cell injury.'],
            ['name' => 'SGOT', 'description' => 'AST enzyme test used in liver and muscle assessment.'],
            ['name' => 'Albumin', 'description' => 'Blood protein test for liver, kidney, and nutrition assessment.'],
            ['name' => 'Phosphorous', 'description' => 'Phosphorus test for bone, kidney, and metabolic balance checks.'],
            ['name' => 'Na+', 'description' => 'Sodium level test for hydration and nerve function assessment.'],
            ['name' => 'K+', 'description' => 'Potassium level test for heart, muscle, and nerve function.'],
            ['name' => 'Ca++', 'description' => 'Calcium test for bone health and muscle and nerve activity.'],
            ['name' => 'Cl-', 'description' => 'Chloride level test for hydration and acid-base balance.'],
            ['name' => 'CBC', 'description' => 'Complete blood count for red cells, white cells, and platelets.'],
            ['name' => 'Hematocrit', 'description' => 'Measures the percentage of red blood cells in blood volume.'],
            ['name' => 'Hemoglobin', 'description' => 'Measures the oxygen-carrying protein in red blood cells.'],
            ['name' => 'Platelet Count', 'description' => 'Checks platelet levels to assess clotting and bleeding risk.'],
            ['name' => 'WBC Differential Count', 'description' => 'Breakdown of white blood cell types for infection clues.'],
            ['name' => 'Blood Typing', 'description' => 'Determines ABO blood group for transfusion compatibility.'],
            ['name' => 'RH Typing', 'description' => 'Determines Rh factor for blood typing purposes.'],
            ['name' => 'Urinalysis', 'description' => 'General urine test for kidney health, infection, and metabolism.'],
            ['name' => 'UCG', 'description' => 'Urine pregnancy test that detects hCG hormone.'],
            ['name' => 'Fecalysis', 'description' => 'Stool examination for parasites, infection, and digestive issues.'],
            ['name' => 'FOBT', 'description' => 'Fecal occult blood test for hidden gastrointestinal bleeding.'],
            ['name' => 'Kato Katz', 'description' => 'Stool microscopy exam for intestinal parasite eggs.'],
            ['name' => 'HBsAg', 'description' => 'Hepatitis B surface antigen screening for active infection.'],
            ['name' => 'Anti-HCV', 'description' => 'Hepatitis C antibody screening test.'],
            ['name' => 'VDRL / RPR', 'description' => 'Non-treponemal screening tests for syphilis.'],
            ['name' => 'Typhidot', 'description' => 'Rapid antibody screening test for typhoid fever.'],
            ['name' => 'H. pylori test', 'description' => 'Detects Helicobacter pylori associated with ulcer disease.'],
            ['name' => 'Dengue Rapid Test', 'description' => 'Rapid screening for dengue infection markers.'],
            ['name' => 'HbA1c', 'description' => 'Average blood glucose level over the past two to three months.'],
            ['name' => 'TSH', 'description' => 'Thyroid-stimulating hormone test for thyroid function assessment.'],
            ['name' => 'T3', 'description' => 'Triiodothyronine hormone test used in thyroid evaluation.'],
            ['name' => 'T4', 'description' => 'Thyroxine hormone test used in thyroid function assessment.'],
            ['name' => 'Troponin I', 'description' => 'Cardiac marker used to detect heart muscle injury.'],
        ];

        DB::table('appointment_types')
            ->whereNotIn('name', array_column($appointmentTypes, 'name'))
            ->delete();

        foreach ($appointmentTypes as $appointmentType) {
            DB::table('appointment_types')->updateOrInsert(
                ['name' => $appointmentType['name']],
                [
                    'description' => $appointmentType['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
