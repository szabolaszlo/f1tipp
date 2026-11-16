<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @var AdminUrlGenerator
     */
    private AdminUrlGenerator $urlGenerator;

    /**
     * @param AdminUrlGenerator $urlGenerator
     */
    public function __construct(AdminUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Route(path: '/admin')]
    public function index(): Response
    {
        return $this->redirect(
            $this->urlGenerator
                ->setController(ResultCrudController::class)
                ->generateUrl()
        );
    }

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

        yield MenuItem::linkTo(ResultCrudController::class, 'admin_result', 'fas fa-flag-checkered');
        yield MenuItem::linkTo(EventCrudController::class, 'admin_events', 'fas fa-calendar-alt');
        yield MenuItem::linkTo(UserCrudController::class, 'admin_menu_users', 'fas fa-users');
        yield MenuItem::linkTo(MessageCrudController::class, 'admin_messages', 'fas fa-envelope');
        yield MenuItem::linkTo(DriverCrudController::class, 'admin_driver', 'fas fa-car');
        yield MenuItem::linkTo(InformationCrudController::class, 'admin_information', 'fas fa-newspaper');
        yield MenuItem::linkTo(SettingCrudController::class, 'admin_setting', 'fas fa-tools');
        yield MenuItem::linkToUrl('back_to_home', 'fas fa-globe-americas', '/');
        yield MenuItem::subMenu('admin_maintenance', 'fas fa-tools')->setSubItems($submenu1);
    }
}
