<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\ReviewForm;
use App\Models\ReviewFormElement;
use Illuminate\Support\Facades\DB;

class ReviewFormTemplateService
{
    /**
     * Get all available templates
     */
    public static function getTemplates(): array
    {
        return [
            'general' => [
                'name' => 'General Review Form',
                'name_id' => 'Formulir Ulasan Umum',
                'description' => 'A comprehensive review form suitable for most academic papers',
                'description_id' => 'Formulir ulasan komprehensif yang cocok untuk sebagian besar makalah akademik',
                'elements' => [
                    [
                        'element_type' => 'rating',
                        'question' => 'How would you rate the originality of this research?',
                        'question_id' => 'Bagaimana Anda menilai originalitas penelitian ini?',
                        'description' => 'Consider the novelty and uniqueness of the research approach',
                        'description_id' => 'Pertimbangkan kebaruan dan keunikan pendekatan penelitian',
                        'required' => true,
                        'sequence' => 1,
                    ],
                    [
                        'element_type' => 'rating',
                        'question' => 'How clear and well-structured is the writing?',
                        'question_id' => 'Seberapa jelas dan terstruktur dengan baik penulisannya?',
                        'description' => 'Evaluate grammar, organization, and readability',
                        'description_id' => 'Evaluasi tata bahasa, organisasi, dan keterbacaan',
                        'required' => true,
                        'sequence' => 2,
                    ],
                    [
                        'element_type' => 'rating',
                        'question' => 'How sound is the methodology?',
                        'question_id' => 'Seberapa kuat metodologinya?',
                        'description' => 'Assess research design and methods',
                        'description_id' => 'Nilai desain dan metode penelitian',
                        'required' => true,
                        'sequence' => 3,
                    ],
                    [
                        'element_type' => 'textarea',
                        'question' => 'What are the major strengths of this paper?',
                        'question_id' => 'Apa kekuatan utama makalah ini?',
                        'required' => true,
                        'sequence' => 4,
                    ],
                    [
                        'element_type' => 'textarea',
                        'question' => 'What are the major weaknesses that need to be addressed?',
                        'question_id' => 'Apa kelemahan utama yang perlu diperbaiki?',
                        'required' => true,
                        'sequence' => 5,
                    ],
                    [
                        'element_type' => 'textarea',
                        'question' => 'Detailed comments and suggestions for improvement',
                        'question_id' => 'Komentar dan saran detail untuk perbaikan',
                        'required' => false,
                        'sequence' => 6,
                    ],
                    [
                        'element_type' => 'radio',
                        'question' => 'Overall recommendation',
                        'question_id' => 'Rekomendasi keseluruhan',
                        'required' => true,
                        'sequence' => 7,
                        'options' => [
                            ['value' => 'accept', 'label' => 'Accept'],
                            ['value' => 'minor_revision', 'label' => 'Minor Revision'],
                            ['value' => 'major_revision', 'label' => 'Major Revision'],
                            ['value' => 'reject', 'label' => 'Reject'],
                        ],
                    ],
                ],
            ],
            'technical' => [
                'name' => 'Technical Review Form',
                'name_id' => 'Formulir Ulasan Teknis',
                'description' => 'Focused on technical aspects and implementation details',
                'description_id' => 'Fokus pada aspek teknis dan detail implementasi',
                'elements' => [
                    [
                        'element_type' => 'rating',
                        'question' => 'Technical soundness and correctness',
                        'question_id' => 'Kebenaran dan kekuatan teknis',
                        'required' => true,
                        'sequence' => 1,
                    ],
                    [
                        'element_type' => 'rating',
                        'question' => 'Implementation quality',
                        'question_id' => 'Kualitas implementasi',
                        'required' => true,
                        'sequence' => 2,
                    ],
                    [
                        'element_type' => 'checkbox',
                        'question' => 'Which technical aspects need improvement? (Select all that apply)',
                        'question_id' => 'Aspek teknis mana yang perlu perbaikan? (Pilih semua yang sesuai)',
                        'required' => false,
                        'sequence' => 3,
                        'options' => [
                            ['value' => 'algorithm', 'label' => 'Algorithm Design'],
                            ['value' => 'data_structure', 'label' => 'Data Structures'],
                            ['value' => 'performance', 'label' => 'Performance Optimization'],
                            ['value' => 'scalability', 'label' => 'Scalability'],
                            ['value' => 'security', 'label' => 'Security Considerations'],
                            ['value' => 'documentation', 'label' => 'Technical Documentation'],
                        ],
                    ],
                    [
                        'element_type' => 'textarea',
                        'question' => 'Technical comments and suggestions',
                        'question_id' => 'Komentar dan saran teknis',
                        'required' => true,
                        'sequence' => 4,
                    ],
                    [
                        'element_type' => 'radio',
                        'question' => 'Recommendation',
                        'question_id' => 'Rekomendasi',
                        'required' => true,
                        'sequence' => 5,
                        'options' => [
                            ['value' => 'accept', 'label' => 'Accept'],
                            ['value' => 'minor_revision', 'label' => 'Minor Revision'],
                            ['value' => 'major_revision', 'label' => 'Major Revision'],
                            ['value' => 'reject', 'label' => 'Reject'],
                        ],
                    ],
                ],
            ],
            'quick' => [
                'name' => 'Quick Review Form',
                'name_id' => 'Formulir Ulasan Cepat',
                'description' => 'A brief form for quick assessments',
                'description_id' => 'Formulir singkat untuk penilaian cepat',
                'elements' => [
                    [
                        'element_type' => 'rating',
                        'question' => 'Overall quality',
                        'question_id' => 'Kualitas keseluruhan',
                        'required' => true,
                        'sequence' => 1,
                    ],
                    [
                        'element_type' => 'textarea',
                        'question' => 'Brief comments',
                        'question_id' => 'Komentar singkat',
                        'required' => true,
                        'sequence' => 2,
                    ],
                    [
                        'element_type' => 'radio',
                        'question' => 'Decision',
                        'question_id' => 'Keputusan',
                        'required' => true,
                        'sequence' => 3,
                        'options' => [
                            ['value' => 'accept', 'label' => 'Accept'],
                            ['value' => 'revise', 'label' => 'Needs Revision'],
                            ['value' => 'reject', 'label' => 'Reject'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Create a form from template
     */
    public static function createFromTemplate(Journal $journal, string $templateKey, string $locale = 'en'): ReviewForm
    {
        $templates = self::getTemplates();
        
        if (!isset($templates[$templateKey])) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found.");
        }

        $template = $templates[$templateKey];
        $isId = $locale === 'id';

        return DB::transaction(function () use ($journal, $template, $isId) {
            // Create form
            $form = ReviewForm::create([
                'journal_id' => $journal->id,
                'title' => $isId ? $template['name_id'] : $template['name'],
                'description' => $isId ? $template['description_id'] : $template['description'],
                'is_active' => true,
            ]);

            // Create elements
            foreach ($template['elements'] as $elementData) {
                ReviewFormElement::create([
                    'review_form_id' => $form->id,
                    'element_type' => $elementData['element_type'],
                    'question' => $isId && isset($elementData['question_id']) ? $elementData['question_id'] : $elementData['question'],
                    'description' => $isId && isset($elementData['description_id']) ? $elementData['description_id'] : ($elementData['description'] ?? null),
                    'required' => $elementData['required'],
                    'sequence' => $elementData['sequence'],
                    'options' => $elementData['options'] ?? null,
                ]);
            }

            return $form;
        });
    }

    /**
     * Export form to JSON
     */
    public static function exportToJson(ReviewForm $form): array
    {
        $form->load(['elements' => function ($query) {
            $query->ordered();
        }]);

        return [
            'name' => $form->title,
            'description' => $form->description,
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'elements' => $form->elements->map(function ($element) {
                return [
                    'element_type' => $element->element_type->value,
                    'question' => $element->question,
                    'description' => $element->description,
                    'required' => $element->required,
                    'sequence' => $element->sequence,
                    'options' => $element->options,
                ];
            })->toArray(),
        ];
    }

    /**
     * Import form from JSON
     */
    public static function importFromJson(Journal $journal, array $data): ReviewForm
    {
        // Validate structure
        if (!isset($data['name']) || !isset($data['elements'])) {
            throw new \InvalidArgumentException('Invalid form data structure.');
        }

        return DB::transaction(function () use ($journal, $data) {
            // Create form
            $form = ReviewForm::create([
                'journal_id' => $journal->id,
                'title' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => false, // Start as inactive
            ]);

            // Create elements
            foreach ($data['elements'] as $elementData) {
                ReviewFormElement::create([
                    'review_form_id' => $form->id,
                    'element_type' => $elementData['element_type'],
                    'question' => $elementData['question'],
                    'description' => $elementData['description'] ?? null,
                    'required' => $elementData['required'] ?? false,
                    'sequence' => $elementData['sequence'],
                    'options' => $elementData['options'] ?? null,
                ]);
            }

            return $form;
        });
    }
}
