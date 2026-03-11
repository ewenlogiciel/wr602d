<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stripeCustomerId to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD stripe_customer_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP stripe_customer_id');
    }
}
