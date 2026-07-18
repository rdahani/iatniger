<?php
/**
 * Catalogue des formations IAT Niger.
 * Contenus extraits du site officiel — source unique pour les pages Formations,
 * la recherche interne et le sitemap.
 */

const NIVEAUX = [
    'niveau-moyen' => [
        'titre' => 'Niveau Moyen',
        'sous_titre' => 'BEP · Bac Professionnel & Technique',
        'recrutement' => 'BEPC, niveau 3ème ou diplôme équivalent',
        'duree' => '3 ans',
        'dossier' => "Extrait d'acte de naissance, certificat de nationalité, dernier bulletin ou diplôme",
        'description' => "Des formations professionnalisantes accessibles dès le BEPC, conçues pour une insertion rapide dans la vie active ou une poursuite d'études vers le BTS et la Licence.",
    ],
    'licence' => [
        'titre' => 'Licences Professionnelles',
        'sous_titre' => 'BTS · Licence (système LMD)',
        'recrutement' => 'BAC toutes séries ou équivalent (L1) · BTS, DUT ou L2 avec 120 crédits (L3)',
        'duree' => '3 ans (BTS en 2 ans, Licence 3 en 1 an)',
        'dossier' => "Extrait d'acte de naissance, certificat de nationalité, dernier bulletin ou diplôme",
        'description' => "13 filières tertiaires et industrielles alignées sur le système LMD et accréditées, pour former les cadres intermédiaires dont l'Afrique a besoin.",
    ],
    'master' => [
        'titre' => 'Masters Professionnels',
        'sous_titre' => 'Master 1 & Master 2 · Accréditations CAMES',
        'recrutement' => 'Licence ou diplôme équivalent (M1) · M1 validé ou maîtrise compatible (M2)',
        'duree' => '2 ans (2 semestres par année)',
        'dossier' => "Extrait d'acte de naissance, certificat de nationalité, dernier bulletin ou diplôme",
        'description' => "9 masters professionnels, dont plusieurs accrédités CAMES, animés par 36 enseignants-chercheurs de rang A issus des grandes universités africaines et internationales.",
    ],
    'doctorat' => [
        'titre' => 'Master de Recherche & Doctorat',
        'sous_titre' => 'Partenariat ESSEC — Université de Douala (Cameroun)',
        'recrutement' => 'Master ou DEA avec une moyenne ≥ 12/20',
        'duree' => 'Master de recherche puis thèse à l\'École Doctorale ESSEC',
        'dossier' => "Demande manuscrite au Recteur précisant l'option, diplômes légalisés depuis le Bac, relevés de notes, pièce d'identité, photo, CV, lettre de motivation, frais d'inscription (300 000 FCFA)",
        'description' => "Un partenariat académique avec l'ESSEC de l'Université de Douala qui ouvre la voie au Master de Recherche et au Doctorat en Sciences économiques et de Gestion, sans quitter Niamey.",
    ],
];

const FORMATIONS = [
    /* ================= NIVEAU MOYEN ================= */
    ['slug' => 'employe-de-banque', 'niveau' => 'niveau-moyen', 'domaine' => 'tertiaire',
        'titre' => 'Employé de Banque', 'icone' => 'landmark',
        'resume' => "Maîtriser les opérations courantes de banque, au guichet comme en back-office.",
        'objectif' => "Former des agents capables de traiter les opérations courantes en front-office (guichetier, conseiller financier) ou en back-office (virements, crédits) : suivi des dossiers clients, saisie et vérification des opérations bancaires.",
        'debouches' => ['Guichetier', 'Agent de back-office', 'Conseiller de clientèle (évolution)', "Directeur d'agence (évolution)"]],
    ['slug' => 'employe-de-commerce', 'niveau' => 'niveau-moyen', 'domaine' => 'tertiaire',
        'titre' => 'Employé de Commerce', 'icone' => 'shopping-bag',
        'resume' => "Polyvalence administrative et commerciale au service des entreprises.",
        'objectif' => "Préparer aux tâches administratives variées : correspondance commerciale, comptabilité, commandes, accueil clients et gestion du secrétariat selon le secteur d'activité.",
        'debouches' => ['Assistant commercial', 'Secrétaire', 'Agent administratif']],
    ['slug' => 'comptabilite-informatique', 'niveau' => 'niveau-moyen', 'domaine' => 'tertiaire',
        'titre' => 'Comptabilité Informatique', 'icone' => 'calculator',
        'resume' => "La comptabilité outillée par l'informatique pour les PME et TPE.",
        'objectif' => "Former des comptables qui contrôlent les dépenses et recettes, donnent leur aval pour achats et investissements, assurent l'évaluation annuelle du bilan comptable et la gestion du personnel en PME/TPE.",
        'debouches' => ['Aide-comptable', 'Comptable PME/TPE', 'Gestionnaire de paie']],
    ['slug' => 'transport-logistique-moyen', 'niveau' => 'niveau-moyen', 'domaine' => 'tertiaire',
        'titre' => 'Transport Logistique', 'icone' => 'truck',
        'resume' => "Les fondamentaux des flux logistiques et du commerce moderne.",
        'objectif' => "Former aux procédures logistiques spécifiques et à la gestion des flux commerciaux modernes, en tenant compte du rôle croissant des infrastructures de transport.",
        'debouches' => ['Agent logistique', 'Magasinier', 'Agent de transit']],
    ['slug' => 'informatique-de-gestion-moyen', 'niveau' => 'niveau-moyen', 'domaine' => 'industriel',
        'titre' => 'Informatique de Gestion', 'icone' => 'monitor',
        'resume' => "Analyse de systèmes et développement d'applications de gestion.",
        'objectif' => "Développer des compétences en analyse des systèmes, développement d'applications, contrôle et gestion de projets, gestion de l'information.",
        'debouches' => ['Technicien informatique', 'Développeur junior', 'Assistant chef de projet']],
    ['slug' => 'maintenance-informatique-moyen', 'niveau' => 'niveau-moyen', 'domaine' => 'industriel',
        'titre' => 'Maintenance Informatique et Électronique', 'icone' => 'wrench',
        'resume' => "Réparer, entretenir et installer les équipements informatiques.",
        'objectif' => "Former à la réparation, l'entretien du matériel, l'installation de logiciels et équipements informatiques, avec un possible rôle de formateur.",
        'debouches' => ['Technicien de maintenance', 'Installateur', 'Formateur technique']],

    /* ================= LICENCE — TERTIAIRE ================= */
    ['slug' => 'droit-gestion-immobiliere', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Droit & Gestion Immobilière', 'icone' => 'building',
        'resume' => "Gérance, gestion locative et management immobilier.",
        'objectif' => "Former à l'insertion professionnelle dans les secteurs de la gérance, de la gestion locative et du management immobilier : gérer les transactions, évaluer bâtiments et ouvrages, maîtriser la négociation immobilière (droit, fiscalité, gestion clientèle).",
        'debouches' => ['Gestionnaire de secteur locatif (public/privé)', 'Syndic de copropriété', 'Administrateur de biens', 'Collaborateur notarial']],
    ['slug' => 'transport-logistique-licence', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Transport Logistique', 'icone' => 'truck',
        'resume' => "Gérer les flux terrestres, maritimes, ferroviaires et aériens.",
        'objectif' => "Répondre aux besoins de procédures logistiques spécifiques face aux mutations des filières industrielles et des échanges commerciaux : gestion des flux de transport terrestre, maritime, fluvial, ferré et aéroporté.",
        'debouches' => ['Responsable logistique', 'Affréteur', 'Responsable d\'entrepôt', 'Déclarant en douane']],
    ['slug' => 'comptabilite-gestion', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Comptabilité & Gestion des Entreprises', 'icone' => 'calculator',
        'resume' => "Tenir, auditer et piloter la comptabilité des entreprises.",
        'objectif' => "Former des professionnels capables de mettre en place une comptabilité en entreprise, enregistrer journal et grand livre, auditer les comptes, produire les états financiers, élaborer des stratégies et réaliser des études de marché — selon les normes internationales.",
        'debouches' => ['Comptable', 'Auditeur junior', 'Contrôleur de gestion', 'Assistant expert-comptable']],
    ['slug' => 'communication-entreprises', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Communication des Entreprises', 'icone' => 'megaphone',
        'resume' => "Stratégie, marketing et communication interne/externe.",
        'objectif' => "Former des cadres à gérer la communication interne et externe des organisations : sciences de l'information-communication, stratégie marketing, maîtrise des outils de communication, plans de publicité pour entreprises industrielles et commerciales.",
        'debouches' => ['Chargé de communication', 'Community manager', 'Attaché de presse', 'Chef de publicité']],
    ['slug' => 'banque-finance-licence', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Banque Finance', 'icone' => 'landmark',
        'resume' => "Culture économique, techniques bancaires et gestion des risques.",
        'objectif' => "Acquérir une culture économique, juridique et fiscale ; consolider les techniques bancaires du marché des particuliers ; identifier les besoins financiers des entreprises, les risques financiers et les stratégies de couverture.",
        'debouches' => ['Chargé de clientèle bancaire', 'Analyste crédit', 'Conseiller financier']],
    ['slug' => 'gestion-commerciale', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Gestion Commerciale', 'icone' => 'trending-up',
        'resume' => "Marketing, techniques de vente et animation d'équipes.",
        'objectif' => "Apporter les compétences en maîtrise des outils marketing, techniques d'analyse, communication et vente : études de marché, stratégies d'entreprise, animation d'équipes de vendeurs, argumentaires et outils de contrôle.",
        'debouches' => ['Responsable commercial', 'Chef des ventes', 'Chargé d\'études marketing']],
    ['slug' => 'grh-licence', 'niveau' => 'licence', 'domaine' => 'tertiaire',
        'titre' => 'Gestion des Ressources Humaines', 'icone' => 'users',
        'resume' => "Double compétence GRH et droit social.",
        'objectif' => "Développer une double compétence en GRH et Droit Social : recrutement, formation, gestion prévisionnelle des emplois, administration des contrats et de la paie, déclarations sociales et fiscales, tableaux de bord sociaux, analyse de la masse salariale.",
        'debouches' => ['Assistant RH', 'Gestionnaire de paie', 'Chargé de recrutement']],

    /* ================= LICENCE — INDUSTRIEL ================= */
    ['slug' => 'exploitation-petroliere', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Exploitation Pétrolière', 'icone' => 'flame',
        'resume' => "Cadres de terrain pour l'industrie pétrolière et parapétrolière.",
        'objectif' => "Répondre aux besoins de cadres de niveau II des entreprises pétrolières : assister les géologues en prospection, évaluer les ressources, préparer le forage, assurer la surveillance géologique, gérer le suivi de production des puits et définir les mesures de sécurité.",
        'debouches' => ['Technicien pétrolier', 'Assistant géologue', 'Superviseur HSE']],
    ['slug' => 'reseaux-telecommunication-licence', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Réseaux & Télécommunication', 'icone' => 'wifi',
        'resume' => "Réseaux informatiques, VoIP et communications sans fil.",
        'objectif' => "Maîtriser les NTIC pour une insertion immédiate dans les réseaux informatiques et télécommunications : architectures téléphonie/VoIP (câblage, protocoles, QoS), communications sans fil, cahiers des charges Télécom, surveillance critique du réseau d'entreprise.",
        'debouches' => ['Administrateur réseaux', 'Technicien télécoms', 'Intégrateur VoIP']],
    ['slug' => 'maintenance-informatique-licence', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Maintenance Informatique & Électronique', 'icone' => 'wrench',
        'resume' => "Diagnostic, plans de maintenance et gestion de parc.",
        'objectif' => "Spécialiser les techniciens au dépannage, à la recherche de pannes et aux réparations : moyens de surveillance et de contrôle, analyse du comportement opérationnel des équipements, plan de maintenance, outils informatiques de gestion de maintenance.",
        'debouches' => ['Responsable maintenance', 'Gestionnaire de parc informatique', 'Technicien supérieur SAV']],
    ['slug' => 'informatique-de-gestion-licence', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Informatique de Gestion', 'icone' => 'monitor',
        'resume' => "Systèmes d'information et logiciels libres (GNU/Linux, MySQL).",
        'objectif' => "Former des techniciens de haut niveau aptes à installer, gérer et faire évoluer les équipements : GNU/Linux et Windows, virtualisation, Merise et UML, administration de bases de données relationnelles, langages web (HTML, JavaScript, PHP, Java, XML).",
        'debouches' => ['Développeur d\'applications', 'Administrateur BDD', 'Concepteur de SI']],
    ['slug' => 'genie-civil', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Génie Civil', 'icone' => 'hard-hat',
        'resume' => "Bâtir les infrastructures du Niger de demain.",
        'objectif' => "Répondre aux exigences du secteur BTP, des cabinets d'études et des agences immobilières dans un contexte d'urbanisation croissante et de changement climatique — programme conçu avec les ordres des urbanistes, architectes, topographes et ingénieurs.",
        'debouches' => ['Conducteur de travaux', 'Technicien bureau d\'études', 'Métreur', 'Chef de chantier']],
    ['slug' => 'genie-logiciel-licence', 'niveau' => 'licence', 'domaine' => 'industriel',
        'titre' => 'Génie Logiciel', 'icone' => 'code',
        'resume' => "Développer les logiciels des PME, grandes sociétés et administrations.",
        'objectif' => "Former des développeurs de haut niveau : méthodologies Merise et UML, administration de SGBD, langages web, sécurité des logiciels, ingénierie multimédia, intégration de bases de données aux sites web et progiciels de gestion intégrés (PGI).",
        'debouches' => ['Développeur logiciel', 'Ingénieur d\'études junior', 'Intégrateur web']],

    /* ================= MASTER — TERTIAIRE ================= */
    ['slug' => 'marketing-logistique', 'niveau' => 'master', 'domaine' => 'tertiaire',
        'titre' => 'Marketing Logistique', 'icone' => 'trending-up',
        'resume' => "Cadres polyvalents en marketing et chaîne logistique.",
        'objectif' => "Former des cadres et techniciens polyvalents répondant aux besoins en marketing et logistique : gestion des stocks et approvisionnements, commerce international, études de marché, e-marketing et gestion d'équipes commerciales.",
        'debouches' => ['Responsable marketing', 'Supply chain manager', 'Responsable achats']],
    ['slug' => 'banque-finance-master', 'niveau' => 'master', 'domaine' => 'tertiaire',
        'titre' => 'Banque Finance', 'icone' => 'landmark',
        'resume' => "Comptabilité, finance, droit et fiscalité intégrés.",
        'objectif' => "Former en deux années des professionnels capables de traiter des problèmes de gestion mêlant aspects comptables, financiers, juridiques et fiscaux : marchés financiers, instruments financiers, gestion des risques bancaires.",
        'debouches' => ['Responsable financier', 'Cadre bancaire', 'Gestionnaire de risques']],
    ['slug' => 'grh-master', 'niveau' => 'master', 'domaine' => 'tertiaire', 'badge' => 'CAMES',
        'titre' => 'Gestion des Ressources Humaines', 'icone' => 'users',
        'resume' => "Manager le capital humain au service de la stratégie. Accrédité CAMES.",
        'objectif' => "Former des managers intégrant les enjeux humains et les stratégies d'entreprise par la mobilisation des ressources collectives. Public : managers, dirigeants, consultants en changement organisationnel.",
        'debouches' => ['DRH', 'Consultant RH', 'Responsable du changement organisationnel']],
    ['slug' => 'transport-logistique-master', 'niveau' => 'master', 'domaine' => 'tertiaire', 'badge' => 'CAMES',
        'titre' => 'Transport Logistique', 'icone' => 'truck',
        'resume' => "Optimiser les flux du commerce international. Accrédité CAMES.",
        'objectif' => "Former des cadres maîtrisant transport, gestion des stocks, technique du commerce international et gestion de production : optimisation qualité/sécurité des flux, planification, affrètement, gestion d'entrepôts.",
        'debouches' => ['Directeur logistique', 'Responsable transport', 'Consultant supply chain']],
    ['slug' => 'gestion-projets', 'niveau' => 'master', 'domaine' => 'tertiaire', 'badge' => 'CAMES',
        'titre' => 'Gestion des Projets', 'icone' => 'clipboard-list',
        'resume' => "Concevoir, organiser et piloter les projets de développement. Accrédité CAMES.",
        'objectif' => "Offrir une formation opérationnelle en gestion de projets avec les outils de conception, d'organisation et de pilotage. Débouchés : spécialistes en gestion de projets et programmes de développement.",
        'debouches' => ['Chef de projet', 'Coordonnateur de programmes', 'Consultant en développement']],
    ['slug' => 'comptabilite-controle-audit', 'niveau' => 'master', 'domaine' => 'tertiaire', 'badge' => 'CAMES',
        'titre' => 'Comptabilité Contrôle Audit', 'icone' => 'calculator',
        'resume' => "L'expertise comptable et l'audit aux normes internationales. Accrédité CAMES.",
        'objectif' => "Former des professionnels capables de traiter des problèmes de gestion mêlant aspects comptables, financiers, juridiques et fiscaux : systèmes comptables, états financiers conformes aux normes internationales, opérations d'inventaire.",
        'debouches' => ['Auditeur', 'Contrôleur de gestion', 'Assistant expert-comptable', 'Chef comptable']],

    /* ================= MASTER — INDUSTRIEL ================= */
    ['slug' => 'reseaux-telecommunication-master', 'niveau' => 'master', 'domaine' => 'industriel',
        'titre' => 'Réseaux & Télécommunication', 'icone' => 'wifi',
        'resume' => "Architectures réseau et déploiement de e-services.",
        'objectif' => "Approfondir les technologies de l'information et de la communication : architecture réseau, déploiement de e-services, conduite de projets, veille technologique et communication multilingue.",
        'debouches' => ['Ingénieur réseaux', 'Architecte télécoms', 'Chef de projet TIC']],
    ['slug' => 'systeme-reseaux', 'niveau' => 'master', 'domaine' => 'industriel',
        'titre' => 'Systèmes & Réseaux', 'icone' => 'server',
        'resume' => "Administrateurs et architectes de niveau ingénieur (Bac+5).",
        'objectif' => "Former des professionnels de l'informatique de niveau Bac+5, statut ingénieur : ingénieurs réseaux-télécoms dotés de méthodes s'adaptant à l'évolution des techniques.",
        'debouches' => ['Administrateur systèmes et réseaux', 'Architecte réseau', 'Ingénieur infrastructure']],
    ['slug' => 'genie-logiciel-master', 'niveau' => 'master', 'domaine' => 'industriel',
        'titre' => 'Génie Logiciel', 'icone' => 'code',
        'resume' => "Ingénierie logicielle, sécurité et systèmes distribués.",
        'objectif' => "Former des spécialistes du conseil aux entreprises en TIC et de la création de sociétés de services en ingénierie informatique : gestion de projets, sécurité informatique, développement logiciel distribué, gestion des données et du Web.",
        'debouches' => ['Ingénieur logiciel', 'Consultant SSII', 'Architecte applicatif', 'Entrepreneur tech']],
];

/** Retourne les formations d'un niveau donné (BDD CMS si dispo). */
function formations_par_niveau(string $niveau): array
{
    if (function_exists('cms_formations') && cms_ready()) {
        return cms_formations($niveau);
    }
    return array_values(array_filter(FORMATIONS, fn ($f) => $f['niveau'] === $niveau));
}

/** Recherche une formation par slug (BDD CMS si dispo). */
function formation_par_slug(string $slug): ?array
{
    if (function_exists('cms_formation_par_slug') && cms_ready()) {
        return cms_formation_par_slug($slug);
    }
    foreach (FORMATIONS as $f) {
        if ($f['slug'] === $slug) {
            return $f;
        }
    }
    return null;
}

/** Niveaux de formation (BDD CMS si dispo). */
function niveaux_catalogue(): array
{
    if (function_exists('cms_niveaux') && cms_ready()) {
        $n = cms_niveaux();
        if ($n) {
            return $n;
        }
    }
    return NIVEAUX;
}
