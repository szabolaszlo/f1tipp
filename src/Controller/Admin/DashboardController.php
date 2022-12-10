<?php

namespace App\Controller\Admin;

use App\Entity\Driver;
use App\Entity\Event;
use App\Entity\Information;
use App\Entity\Message;
use App\Entity\Result;
use App\Entity\Setting;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('F1Tipp Admin');
    }

    public function configureCrud(): Crud
    {
        return Crud::new();
    }

    public function configureMenuItems(): iterable
    {
        $submenu1 = [
            MenuItem::linkToRoute('admin_maintenance_recalculate_points', 'fas fa-calculator', 're_calculate_points'),
            MenuItem::linkToRoute('admin_maintenance_cache_clear', 'fas fa-trash-alt', 'cache_clear'),
        ];

        yield MenuItem::linkToCrud('admin_result', 'fas fa-flag-checkered', Result::class);
        yield MenuItem::linkToCrud('admin_events', 'fas fa-calendar-alt', Event::class);
        yield MenuItem::linkToCrud('admin_menu_users', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('admin_messages', 'fas fa-envelope', Message::class);
        yield MenuItem::linkToCrud('admin_driver', 'fas fa-car', Driver::class);
        yield MenuItem::linkToCrud('admin_information', 'fas fa-newspaper', Information::class);
        yield MenuItem::linkToCrud('admin_setting', 'fas fa-tools', Setting::class);
        yield MenuItem::linkToUrl('back_to_home', 'fas fa-globe-americas', '/');
        yield MenuItem::subMenu('admin_maintenance', 'fas fa-tools')->setSubItems($submenu1);
    }
}
