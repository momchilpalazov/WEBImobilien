<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Transaction;

class TransactionController extends BaseAdminController
{
    private TransactionRepositoryInterface $transactionRepository;
    private PropertyRepositoryInterface $propertyRepository;
    private ClientRepositoryInterface $clientRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->transactionRepository = Container::resolve(TransactionRepositoryInterface::class);
        $this->propertyRepository = Container::resolve(PropertyRepositoryInterface::class);
        $this->clientRepository = Container::resolve(ClientRepositoryInterface::class);
        $this->userRepository = Container::resolve(UserRepositoryInterface::class);
    }

    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page', 1));
        $filters = [
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'agent_id' => $request->get('agent_id'),
            'start_date' => $request->get('start_date', date('Y-m-01')), // First day of current month
            'end_date' => $request->get('end_date', date('Y-m-t')), // Last day of current month
        ];

        $sorting = [
            'field' => $request->get('sort_by', 'transaction_date'),
            'direction' => $request->get('sort_dir', 'DESC')
        ];

        $transactions = $this->transactionRepository->findAll($filters, $sorting, $page);
        $totals = $this->transactionRepository->getTotalsByPeriod(
            $filters['start_date'],
            $filters['end_date'],
            $filters['type']
        );

        // Get agents for filter
        $agents = $this->userRepository->findAllAgents();

        return $this->render('admin/transactions/index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'sorting' => $sorting,
            'totals' => $totals,
            'page' => $page,
            'agents' => $agents
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->isPost()) {
            $transaction = new Transaction();
            $this->bindTransactionData($transaction, $request);
            
            // Calculate commission if applicable
            $transaction->calculateCommission();
            
            $this->transactionRepository->create($transaction);
            
            $this->addFlash('success', 'Транзакцията е създадена успешно');
            return $this->redirect('/admin/transactions');
        }

        // Load data for form
        $properties = $this->propertyRepository->findAll();
        $clients = $this->clientRepository->findAll();
        $agents = $this->userRepository->findAllAgents();

        return $this->render('admin/transactions/create', [
            'transaction' => new Transaction(),
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $transaction = $this->transactionRepository->findById($id);
        if (!$transaction) {
            throw new \RuntimeException('Транзакцията не е намерена');
        }

        if ($request->isPost()) {
            $this->bindTransactionData($transaction, $request);
            
            // Recalculate commission if applicable
            $transaction->calculateCommission();
            
            $this->transactionRepository->update($transaction);
            
            $this->addFlash('success', 'Транзакцията е обновена успешно');
            return $this->redirect('/admin/transactions');
        }

        // Load data for form
        $properties = $this->propertyRepository->findAll();
        $clients = $this->clientRepository->findAll();
        $agents = $this->userRepository->findAllAgents();

        return $this->render('admin/transactions/edit', [
            'transaction' => $transaction,
            'properties' => $properties,
            'clients' => $clients,
            'agents' => $agents
        ]);
    }

    public function delete(Request $request, int $id): Response
    {
        if ($request->isPost()) {
            $transaction = $this->transactionRepository->findById($id);
            if (!$transaction) {
                throw new \RuntimeException('Транзакцията не е намерена');
            }

            $this->transactionRepository->delete($id);
            $this->addFlash('success', 'Транзакцията е изтрита успешно');
        }

        return $this->redirect('/admin/transactions');
    }

    private function bindTransactionData(Transaction $transaction, Request $request): void
    {
        $transaction->type = $request->get('type');
        $transaction->property_id = $request->get('property_id') ?: null;
        $transaction->client_id = $request->get('client_id') ?: null;
        $transaction->agent_id = $request->get('agent_id') ?: null;
        $transaction->amount = (float) $request->get('amount');
        $transaction->currency = $request->get('currency', 'EUR');
        $transaction->commission_rate = $request->get('commission_rate') ? (float) $request->get('commission_rate') : null;
        $transaction->status = $request->get('status', 'pending');
        $transaction->description = $request->get('description');
        $transaction->transaction_date = $request->get('transaction_date', date('Y-m-d'));
        $transaction->due_date = $request->get('due_date') ?: null;
        $transaction->payment_method = $request->get('payment_method');
        $transaction->reference_number = $request->get('reference_number');
        $transaction->created_by = $this->getCurrentUser()->id;
    }
} 