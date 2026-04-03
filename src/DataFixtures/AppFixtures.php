<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\Tool;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // ══════════════════════════════════════════
        // PLANS
        // ══════════════════════════════════════════

        $planFree = new Plan();
        $planFree->setName("FREE");
        $planFree->setDescription("Abonnement gratuit");
        $planFree->setPrice(0);
        $planFree->setUsageLimit(2);
        $planFree->setRole("ROLE_FREE");
        $planFree->setActive(true);
        $manager->persist($planFree);

        $planBasic = new Plan();
        $planBasic->setName("BASIC");
        $planBasic->setDescription("Abonnement basic : 20 générations par jour");
        $planBasic->setPrice(9.9);
        $planBasic->setUsageLimit(20);
        $planBasic->setRole("ROLE_BASIC");
        $planBasic->setActive(true);
        $planBasic->setStripePriceId("price_1T9odFPeBcEMXzrAUCgKrvYP");
        $manager->persist($planBasic);

        $planPremium = new Plan();
        $planPremium->setName("PREMIUM");
        $planPremium->setDescription("Abonnement PREMIUM : 200 générations par jour");
        $planPremium->setPrice(45);
        $planPremium->setUsageLimit(200);
        $planPremium->setRole("ROLE_PREMIUM");
        $planPremium->setActive(true);
        $planPremium->setStripePriceId("price_1T9og8PeBcEMXzrA1BlvwJtf");
        $manager->persist($planPremium);

        // ══════════════════════════════════════════
        // TOOLS
        // Disponibilité cumulative :
        //   FREE  → [FREE, BASIC, PREMIUM]
        //   BASIC → [BASIC, PREMIUM]
        //   PREMIUM → [PREMIUM]
        // ══════════════════════════════════════════

        $tools = [

            // ── Disponibles sur FREE (et donc tous les plans) ──────────────
            [
                'name'        => 'Éditeur WYSIWYG',
                'slug'        => 'wysiwyg',
                'icon'        => 'fa-solid fa-pen-nib',
                'description' => 'Rédigez et mettez en forme votre contenu directement dans le navigateur, puis exportez en PDF.',
                'color'       => '#10b981',
                'plans'       => [$planFree, $planBasic, $planPremium],
            ],
            [
                'name'        => 'URL vers PDF',
                'slug'        => 'url',
                'icon'        => 'fa-solid fa-globe',
                'description' => 'Convertit n\'importe quelle URL en PDF fidèle, rendu via Chromium.',
                'color'       => '#4a9eff',
                'plans'       => [$planFree, $planBasic, $planPremium],
            ],
            [
                'name'        => 'HTML vers PDF',
                'slug'        => 'html',
                'icon'        => 'fa-solid fa-code',
                'description' => 'Transforme un fichier HTML (et ses assets CSS/JS) en document PDF.',
                'color'       => '#ff6b35',
                'plans'       => [$planFree, $planBasic, $planPremium],
            ],
            [
                'name'        => 'Markdown vers PDF',
                'slug'        => 'markdown',
                'icon'        => 'fa-solid fa-hashtag',
                'description' => 'Convertit un fichier Markdown en PDF structuré et lisible.',
                'color'       => '#a78bfa',
                'plans'       => [$planFree, $planBasic, $planPremium],
            ],

            // ── Disponibles sur BASIC et PREMIUM ──────────────────────────
            [
                'name'        => 'Word vers PDF',
                'slug'        => 'word',
                'icon'        => 'fa-solid fa-file-word',
                'description' => 'Convertit les fichiers .docx et .doc en PDF via LibreOffice.',
                'color'       => '#2b7cd3',
                'plans'       => [$planBasic, $planPremium],
            ],
            [
                'name'        => 'Excel vers PDF',
                'slug'        => 'excel',
                'icon'        => 'fa-solid fa-file-excel',
                'description' => 'Convertit les feuilles de calcul .xlsx et .xls en PDF.',
                'color'       => '#1e7e45',
                'plans'       => [$planBasic, $planPremium],
            ],
            [
                'name'        => 'PowerPoint vers PDF',
                'slug'        => 'powerpoint',
                'icon'        => 'fa-solid fa-file-powerpoint',
                'description' => 'Convertit les présentations .pptx et .ppt en PDF.',
                'color'       => '#c43e1c',
                'plans'       => [$planBasic, $planPremium],
            ],
            [
                'name'        => 'Texte brut vers PDF',
                'slug'        => 'texte',
                'icon'        => 'fa-solid fa-file-lines',
                'description' => 'Convertit les fichiers .txt en PDF avec mise en forme propre.',
                'color'       => '#6b7280',
                'plans'       => [$planBasic, $planPremium],
            ],

            // ── Disponibles sur PREMIUM uniquement ────────────────────────
            [
                'name'        => 'ODT vers PDF',
                'slug'        => 'odt',
                'icon'        => 'fa-solid fa-file',
                'description' => 'Convertit les documents LibreOffice Writer (.odt) en PDF.',
                'color'       => '#1a73e8',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'ODS vers PDF',
                'slug'        => 'ods',
                'icon'        => 'fa-solid fa-table',
                'description' => 'Convertit les feuilles LibreOffice Calc (.ods) en PDF.',
                'color'       => '#188038',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'ODP vers PDF',
                'slug'        => 'odp',
                'icon'        => 'fa-solid fa-file-image',
                'description' => 'Convertit les présentations LibreOffice Impress (.odp) en PDF.',
                'color'       => '#b5370a',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'RTF vers PDF',
                'slug'        => 'rtf',
                'icon'        => 'fa-solid fa-align-left',
                'description' => 'Convertit les fichiers Rich Text Format (.rtf) en PDF.',
                'color'       => '#7c3aed',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'CSV vers PDF',
                'slug'        => 'csv',
                'icon'        => 'fa-solid fa-file-csv',
                'description' => 'Convertit les fichiers de données .csv en tableau PDF.',
                'color'       => '#0d9488',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'Image vers PDF',
                'slug'        => 'image',
                'icon'        => 'fa-solid fa-image',
                'description' => 'Convertit les images .png, .jpg, .jpeg en document PDF.',
                'color'       => '#db2777',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'SVG vers PDF',
                'slug'        => 'svg',
                'icon'        => 'fa-solid fa-bezier-curve',
                'description' => 'Convertit les graphiques vectoriels .svg en PDF haute qualité.',
                'color'       => '#d97706',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'Fusionner des PDF',
                'slug'        => 'fusionner',
                'icon'        => 'fa-solid fa-layer-group',
                'description' => 'Fusionne plusieurs fichiers PDF en un seul document.',
                'color'       => '#ff3b30',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'PDF vers PDF/A',
                'slug'        => 'pdfa',
                'icon'        => 'fa-solid fa-shield-halved',
                'description' => 'Convertit un PDF en format d\'archivage longue durée PDF/A.',
                'color'       => '#4f46e5',
                'plans'       => [$planPremium],
            ],
            [
                'name'        => 'Capture d\'écran URL',
                'slug'        => 'capture',
                'icon'        => 'fa-solid fa-camera',
                'description' => 'Génère une capture d\'écran PNG/JPEG d\'une page web via Chromium.',
                'color'       => '#0891b2',
                'plans'       => [$planPremium],
            ],
        ];

        foreach ($tools as $data) {
            $tool = new Tool();
            $tool->setName($data['name']);
            $tool->setSlug($data['slug']);
            $tool->setIcon($data['icon']);
            $tool->setDescription($data['description']);
            $tool->setColor($data['color']);
            $tool->setIsActive(true);

            foreach ($data['plans'] as $plan) {
                $tool->addPlan($plan);
            }

            $manager->persist($tool);
        }

        // ══════════════════════════════════════════
        // UTILISATEUR DE TEST
        // ══════════════════════════════════════════

        $testUser = new User();
        $testUser->setEmail('test@gmail.com');
        $testUser->setFirstname('Test');
        $testUser->setLastname('User');
        $testUser->setDob(new \DateTimeImmutable('1990-01-01'));
        $testUser->setIsVerified(true);
        $testUser->setPlan($planBasic);
        $testUser->setPassword($this->hasher->hashPassword($testUser, '123456'));
        $manager->persist($testUser);

        $manager->flush();
    }
}
