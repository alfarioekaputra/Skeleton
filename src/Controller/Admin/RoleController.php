<?php

declare(strict_types=1);

namespace KejawenLab\Semart\Skeleton\Controller\Admin;

use KejawenLab\Semart\Skeleton\Entity\Group;
use KejawenLab\Semart\Skeleton\Entity\Role;
use KejawenLab\Semart\Skeleton\Repository\GroupRepository;
use KejawenLab\Semart\Skeleton\Repository\RoleRepository;
use KejawenLab\Semart\Skeleton\Request\RequestHandler;
use KejawenLab\Semart\Skeleton\Security\Authorization\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/roles")
 *
 * @Permission(menu="GROUP")
 *
 * @author Muhamad Surya Iksanudin <surya.iksanudin@gmail.com>
 */
class RoleController extends AdminController
{
    /**
     * @Route("/user_roles", methods={"GET"}, name="user_roles", options={"expose"=true})
     *
     * @Permission(actions={Permission::ADD, Permission::EDIT})
     */
    public function userRoles(Request $request, GroupRepository $groupRepository, RoleRepository $roleRepository)
    {
        $group = $groupRepository->find($request->query->get('groupId'));
        if (!$group instanceof Group) {
            throw new NotFoundHttpException();
        }

        $roles = $roleRepository->findRolesByGroup($group, $request->query->get('q', ''));

        $table = $this->renderView('role/table-content.html.twig', ['roles' => $roles]);

        return new JsonResponse([
            'table' => $table,
        ]);
    }

    /**
     * @Route("/save", methods={"POST"}, name="roles_save", options={"expose"=true})
     *
     * @Permission(actions={Permission::ADD, Permission::EDIT})
     */
    public function save(Request $request, RoleRepository $repository, RequestHandler $requestHandler)
    {
        $primary = $request->get('id');
        if ($primary) {
            $role = $repository->find($primary);
        } else {
            $role = new Role();
        }

        $requestHandler->handle($request, $role);
        if (!$requestHandler->isValid()) {
            return new JsonResponse(['status' => 'KO', 'errors' => $requestHandler->getErrors()]);
        }

        $this->commit($role);

        return new JsonResponse(['status' => 'OK']);
    }
}
