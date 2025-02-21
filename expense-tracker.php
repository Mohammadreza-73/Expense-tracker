#!/usr/bin/env php
<?php

// TODO:
// Users can view a summary of expenses for a specific month (of current year).

$fileName = 'expenses.json';
$action = $argv[1];

function now(string $format = 'Y-m-d H:i:s'): string
{
    return date($format);
}

function loadExpenses(): array
{
    global $fileName;
    if (! file_exists($fileName)) {
        return [];
    }

    return json_decode(file_get_contents($fileName), true);
}

function getLastId(): int
{
    $expenses = loadExpenses();

    if (empty($expenses)) {
        return 0;
    }

    return end($expenses)['ID'];
}

function saveExpense($expense): void
{
    global $fileName;
    file_put_contents($fileName, json_encode($expense, JSON_PRETTY_PRINT));
}

function addExpense(string $description, int $amount): int
{
    $expenses = loadExpenses();

    $expenses[] = [
        'ID' => getLastId() + 1,
        'description' => $description,
        'amount' => $amount,
        'createdAt' => now(),
        'updatedAt' => now(),
    ];

    saveExpense($expenses);

    return getLastId();
}

function updateExpense(int $id, string $description, int $amount): void
{
    $expenses = loadExpenses();

    foreach ($expenses as &$expense) {
        if ($expense['ID'] === $id) {
            $expense['description'] = $description;
            $expense['amount'] = $amount;
            $expense['updatedAt'] = now();

            break;
        }
    }

    saveExpense($expenses);
}

function deleteExpense(int $id): void
{
    $expenses = loadExpenses();

    $expenses = array_filter($expenses, function ($expense) use ($id) {
        return $expense['ID'] !== $id;
    });

    saveExpense($expenses);
}

function listExepnses(): void
{
    $expenses = loadExpenses();

    echo "ID  Date                 Description  Amount\n";
    foreach ($expenses as $expense) {
        echo "{$expense['ID']}   {$expense['updatedAt']}  {$expense['description']}        {$expense['amount']}\n";
    }
}

function summery(): int
{
    $expenses = loadExpenses();
    $total = 0;

    foreach ($expenses as $expense) {
        $total += $expense['amount'];
    }

    return $total;
}

function showHelp(): void
{
    echo "Expense Tracker - Manage your finances\n";
    echo "Usage: php expense-tracker.php <command> [options]\n\n";
    echo "Commands:\n";
    echo "  add       Add a new expense\n";
    echo "           --description <description>  Description of the expense\n";
    echo "           --amount <amount>           Amount of the expense\n\n";
    echo "  update    Update an existing expense\n";
    echo "           --id <id>                   ID of the expense to update\n";
    echo "           --description <description> New description\n";
    echo "           --amount <amount>           New amount\n\n";
    echo "  delete    Delete an expense\n";
    echo "           --id <id>                   ID of the expense to delete\n\n";
    echo "  list      List all expenses\n\n";
    echo "  summary   Show summary of expenses\n";
    echo "           --month <month>             (Optional) Filter by month (1-12)\n\n";
    echo "  help      Show this help message\n";
}

switch ($action) {
    case 'add':
        if (! isset($argv[2]) && $argv[2] !== '--description') {
            echo "Missing required option [description].";
            exit(0);
        }

        if (! isset($argv[4]) && $argv[4] !== '--amount') {
            echo "Missing required option [amount].";
            exit(0);
        }

        $description = $argv[3];
        $amount = $argv[5];
        $expenseId = addExpense($description, $amount);

        echo "Expense added successfully (ID: {$expenseId})\n";

        break;

    case 'update':
        if (! isset($argv[2]) && $argv[2] !== '--id') {
            echo "Missing required option [id].";
            exit(0);
        }

        if (! isset($argv[4]) && $argv[4] !== '--description') {
            echo "Missing required option [description].";
            exit(0);
        }

        if (! isset($argv[6]) && $argv[6] !== '--amount') {
            echo "Missing required option [amount].";
            exit(0);
        }

        $id = $argv[3];
        $description = $argv[5];
        $amount = $argv[7];
        updateExpense($id, $description, $amount);

        echo "Expense updated successfully (ID: $id)\n";

        break;

    case 'delete':
        if (! isset($argv[2]) && $argv[2] !== '--id') {
            echo "Missing required option [id].";
            exit(0);
        }

        $id = $argv[3];
        deleteExpense($id);

        echo "Expense deleted successfully (ID: $id)\n";

        break;

    case 'list':
        listExepnses();

        break;

    case 'summery':
        $total = summery();

        echo "Total expenses: $$total\n";

        break;

    case 'help':
        showHelp();
        break;
    
    default:
        showHelp();
        break;
}
