<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Task;
use App\Entity\Categories;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class TaskControlerController extends AbstractController
{
    #[Route('/task/controler', name: 'task_controler')]
    public function index(): Response
    {
        return $this->render('task_controler/index.html.twig', [
            'controller_name' => 'TaskControlerController',
        ]);
    }
	#[Route('/task/create', name: 'task_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {

		$entityManager = $doctrine->getManager();
        // creates a task object and initializes some data for this example
        $task = new Task();
        $task->setNameTask('Tapez le nom de votre tâche');
        $task->setDescriptionTask('écrivez une description');
		$task->setDueDateTask(new \DateTime('tomorrow'));
		$task->setPriorityTask('Choisir la priorité');

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class,['attr' => ['class' => 'form-control']])
            ->add('descriptionTask', TextareaType::class,['attr' => ['class' => 'form-control']])
			->add('dueDateTask', DateType::class,["widget"=>"single_text",'attr' => ['class' => 'form-control']])
			->add('priorityTask', ChoiceType::class, [
                'attr' => ['class' => 'form-select'],
                    'choices'  => [
                        'Haute' => 'haute',
                        'Normale' => 'normal',
                        'Basse' => 'basse',
                    ],
            ])

			->add('category', EntityType::class, [
							// looks for choices from this entity
							'class' => Categories::class,

							// uses the User.username property as the visible option string
							'choice_label' => 'libelleCategory',

							// used to render a select box, check boxes or radios
							// 'multiple' => true,
							// 'expanded' => true,
						])
            ->add('save', SubmitType::class, ['label' => 'Create Task','attr' => ['class' => 'btn btn-primary']])

            ->getForm();

		        $form->handleRequest($request);
					if ($form->isSubmitted() && $form->isValid()) {
						// $form->getData() holds the submitted values
						// but, the original `$task` variable has also been updated
						$task = $form->getData();
			

		$task->setCreatedDateTask(new \DateTime('today'));
						// ... perform some action, such as saving the task to the database
				 // tell Doctrine you want to (eventually) save the Product (no queries yet)
        		$entityManager->persist($task);

				// actually executes the queries (i.e. the INSERT query)
				$entityManager->flush();
				$this->addFlash('success', 'Tâche créée !');

						return $this->redirectToRoute('task_listing');
					}
        return $this->renderForm('task_controler/create.html.twig', [
            'form' => $form,
        ]);
    }

	#[Route('/task/listing', name: 'task_listing')]
    public function listing(ManagerRegistry $doctrine): Response
    {
		$tasks = $doctrine->getRepository(task::class)->findAll();
        return $this->render('task_controler/listing.html.twig', [
            'tasks' => $tasks,
        ]);
    }

#[Route('/task/edit/{id}', name: 'task_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $task = $entityManager->getRepository(task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class, ['label' => 'Nom de la tâche :', 'attr' => ['class' => 'form-control mb-4']])
            ->add('descriptionTask', TextareaType::class, ['label' => 'Description de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('dueDateTask', DateType::class, ["widget"=>"single_text",'label' => 'Date création de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('priorityTask', ChoiceType::class, ['label' => 'Priorité de la tâche :','choices' => ['Haute' => 'Haute','Normal' => 'Normal','Basse' => 'Basse',], 'attr' => ['class' => 'form-select mb-4'],])
            ->add('category', EntityType::class, ['label' => 'Catégorie de la tâche :', 'class' => Categories::class,'choice_label' => 'libelleCategory', 'attr' => ['class' => 'form-select mb-4'],])
            ->add('save', SubmitType::class, ['label' => 'Modifier la tâche','attr' => ['class' => 'btn btn-primary']])
            ->getForm();
		$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				// $form->getData() holds the submitted values
				// but, the original $task variable has also been updated
				$task = $form->getData();

				$task->setCreatedDateTask(new \DateTime('today'));

				// ... perform some action, such as saving the task to the database
				$entityManager->persist($task);

				// actually executes the queries (i.e. the INSERT query)
				$entityManager->flush();
				 $this->addFlash('success', 'Tâche modifiée !');
				return $this->redirectToRoute('task_listing');
			}

        return $this->renderForm('task_controler/edit.html.twig', [
            'form' => $form,
        ]);
    }

		#[Route('/task_controler/delete/{id}', name: 'task_delete')]
		public function remove(ManagerRegistry $doctrine, int $id): Response
		{
			$entityManager = $doctrine->getManager();
			$task = $entityManager->getRepository(task::class)->find($id);
			$entityManager->remove($task);
			$entityManager->flush();
			$this->addFlash('danger', 'Tâche supprimée !');

			return $this->redirectToRoute('task_listing');
				
		}
}