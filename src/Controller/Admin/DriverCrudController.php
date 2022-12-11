<?php

namespace App\Controller\Admin;

use App\Controller\Module\ResultsOfChampionshipController;
use App\Entity\Driver;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DriverCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Driver::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncDriverPoints = Action::new('syncPoints', 'admin_driver_editor_sync', 'fa fa-sync')
            ->linkToCrudAction('syncDriverPoints', [])
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $syncDriverPoints);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_driver_editor')
            ->setSearchFields(['id', 'name', 'short', 'point'])
            ->setPaginatorPageSize(50)
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status');
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $short = TextField::new('short');
        $point = IntegerField::new('point');
        $status = Field::new('status');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $short, $point, $status];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $short, $point, $status];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $short, $point, $status];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $short, $point, $status];
        }

        return [];
    }

    //TODO Turn into service or usecase

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function syncDriverPoints(Request $request): RedirectResponse
    {
        $response = json_decode(file_get_contents(ResultsOfChampionshipController::DRIVER_JSON_PATH), true);

        $standings = $response['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];

        foreach ($standings as $standing) {
            $point = $standing['points'];
            $driverShort = $standing['Driver']['code'];

            $driverEntity = $this->doctrine
                ->getRepository('App\Entity\Driver')
                ->findOneBy(array('short' => $driverShort));

            if ($driverEntity) {
                $driverEntity->setPoint($point);
                $this->doctrine->getManager()->persist($driverEntity);
            }
        }

        $this->doctrine->getManager()->flush();

        $this->addFlash('success', 'admin_driver_editor_sync_success');

        return $this->redirect($request->headers->get('referer'));
    }
}
