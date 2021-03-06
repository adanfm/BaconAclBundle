<?php

namespace Bacon\Bundle\AclBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Bacon\Bundle\CoreBundle\Controller\AdminController;
/**
 * ModuleActions controller.
 *
 * @Route("/module-actions")
 */
class ModuleActionsController extends AdminController
{

    /**
     * Lists all ModuleActions entities.
     *
     * @Route("/",defaults={"page"=1, "sort"="id", "direction"="asc"}, name="module_actions")
     * @Route("/page/{page}/sort/{sort}/direction/{direction}/", defaults={"page"=1, "sort"="id", "direction"="asc"}, name="module_actions_pagination")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction($page, $sort, $direction)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'INDEX')) {
            throw $this->createAccessDeniedException();
        }

        $breadcumbs = $this->container->get('bacon_breadcrumbs');

        $breadcumbs->addItem([
            'title' => 'ModuleActions',
            'route' => '',
        ]);

        $breadcumbs->addItem([
            'title' => 'List',
            'route' => '',
        ]);

        $entityName     =   $this->getParameter('bacon_acl.entities.module_actions');
        $formTypeName   =   $this->getParameter('bacon_acl.forms.module_actions_form_type_class');

        $entity = new $entityName();

        if ($this->get('session')->has('moduleactions_search_session')) {
            $objSerialize = $this->get('session')->get('moduleactions_search_session');
            $entity = unserialize($objSerialize);

            if (!is_null($entity->getModule())) {
                $this->getDoctrine()->getManager()->persist($entity->getModule());
            }
        }

        $query = $this->getRepository()->getQueryPagination($entity, $sort, $direction);


        $paginator = $this->getPagination($query, $page, $entityName::PER_PAGE);

        $paginator->setUsedRoute('module_actions_pagination');

        $form = $this->createForm($formTypeName, $entity, [
            'search' => true,
        ]);

        return [
            'pagination'  => $paginator,
            'form_search' => $form->createView(),
            'form_delete' => $this->createDeleteForm()->createView(),
        ];
    }

   /**
    * Search filter ModuleActions entities.
    *
    * @Route("/search", name="module_actions_search")
    * @Method({"POST","GET"})
    * @Security("has_role('ROLE_ADMIN')")
    * @Template()
    */
    public function searchAction(Request $request)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'INDEX')) {
            throw $this->createAccessDeniedException();
        }

        $this->get('session')->remove('moduleactions_search_session');

        if ($request->getMethod() === Request::METHOD_POST) {
            $entityName     =   $this->getParameter('bacon_acl.entities.module_actions');
            $formTypeName   =   $this->getParameter('bacon_acl.forms.module_actions_form_type_class');

            $form = $this->createForm($formTypeName, new $entityName(), [
                'search' => true,
            ]);

            $form->handleRequest($request);

            $this->get('session')->set('moduleactions_search_session', serialize($form->getData()));
        }

        return $this->redirect($this->generateUrl('module_actions'));
    }

    /**
     * Displays a form to create a new ModuleActions entity.
     *
     * @Route("/new", name="module_actions_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'NEW')) {
            throw $this->createAccessDeniedException();
        }

        $entityName         =   $this->getParameter('bacon_acl.entities.module_actions');
        $formTypeName       =   $this->getParameter('bacon_acl.forms.module_actions_form_type_class');
        $formHandlerName    =   $this->getParameter('bacon_acl.forms.module_actions_form_handler_class');

        $breadcumbs = $this->container->get('bacon_breadcrumbs');

        $breadcumbs->addItem([
            'title' => 'ModuleActions',
            'route' => 'module_actions',
        ]);

        $breadcumbs->addItem([
            'title' => 'New',
            'route' => '',
        ]);

        $form = $this->createForm($formTypeName, new $entityName());

        $form->add('add_all_actions', CheckboxType::class, [
            'required'  => false,
            'label'     => 'Adicionar em todas as actions',
            'mapped'    => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $handler = new $formHandlerName(
                $form,
                $this->getDoctrine()->getManager(),
                $this->get('session')->getFlashBag()
            );

            $handler->setRequest($request);

            if ($handler->save()) {
                return $this->redirect($this->generateUrl('module_actions'));
            }
        }

        return [
            'form'   => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing ModuleActions entity.
     *
     * @Route("/{id}/edit", name="module_actions_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $formTypeName       =   $this->getParameter('bacon_acl.forms.module_actions_form_type_class');
        $formHandlerName    =   $this->getParameter('bacon_acl.forms.module_actions_form_handler_class');

        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            return $this->createNotFoundException('Entity not Exists!');
        }

        $breadcumbs = $this->container->get('bacon_breadcrumbs');

        $breadcumbs->addItem([
            'title' => 'ModuleActions',
            'route' => 'module_actions',
        ]);

        $breadcumbs->addItem([
            'title' => 'Edit',
            'route' => '',
        ]);

        $form = $this->createForm($formTypeName, $entity);
        $deleteForm = $this->createDeleteForm('module_actions_delete', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $handler = new $formHandlerName(
                $form,
                $this->getDoctrine()->getManager(),
                $this->get('session')->getFlashBag()
            );

            if ($entity = $handler->save()) {
                return $this->redirect($this->generateUrl('module_actions'));
            }
        }

        return [
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }
    
    /**
     * Finds and displays a ModuleActions entity.
     *
     * @Route("/{id}", name="module_actions_show")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function showAction($id)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'SHOW')) {
            throw $this->createAccessDeniedException();
        }

        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            return $this->createNotFoundException('Entity not Exists!');
        }

        $breadcumbs = $this->container->get('bacon_breadcrumbs');

        $breadcumbs->addItem([
            'title' => 'ModuleActions',
            'route' => 'module_actions',
        ]);

        $breadcumbs->addItem([
            'title' => 'Details',
            'route' => '',
        ]);

        $deleteForm = $this->createDeleteForm('module_actions_delete', $entity);

        return [
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ];
    }
    
    /**
     * Deletes a ModuleActions entity.
     *
     * @Route("/{id}", name="module_actions_delete")
     * @Security("has_role('ROLE_ADMIN')")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $acl = $this->get('bacon_acl.service.authorization');

        if (!$acl->authorize('module-actions', 'DELETE')) {
            throw $this->createAccessDeniedException();
        }

        $formHandlerName    =   $this->getParameter('bacon_acl.forms.module_actions_form_handler_class');

        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            return $this->createNotFoundException('Entity not Exists!');
        }

        $handler = new $formHandlerName(
            $this->createDeleteForm('module_actions_delete', $entity),
            $this->get('doctrine')->getManager(),
            $this->get('session')->getFlashBag()
        );

        $handler->delete($entity);

        return $this->redirect($this->generateUrl('module_actions'));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        $entity = $this->getParameter('bacon_acl.entities.module_actions');
        return $this->getDoctrine()->getRepository($entity);
    }

}
