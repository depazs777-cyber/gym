<?php

namespace App\Controllers;

use App\Core\Controller;

class ReportsController extends Controller {

    public function index() {
        $this->view('accounting/reports');
    }

    public function balanceSheet() {
        $gymId = $_SESSION['gym_id'];

        // Activos (Clase 1)
        $assets = $this->getAccountBalances($gymId, '1');

        // Pasivos (Clase 2)
        $liabilities = $this->getAccountBalances($gymId, '2');

        // Patrimonio (Clase 3)
        $equity = $this->getAccountBalances($gymId, '3');

        $this->view('accounting/report_balance', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $this->sumBalances($assets),
            'total_liabilities' => $this->sumBalances($liabilities),
            'total_equity' => $this->sumBalances($equity)
        ]);
    }

    public function incomeStatement() {
        $gymId = $_SESSION['gym_id'];

        // Ingresos (Clase 4)
        $revenue = $this->getAccountBalances($gymId, '4');

        // Gastos (Clase 5)
        $expenses = $this->getAccountBalances($gymId, '5');

        // Costos (Clase 6 y 7)
        $costs = $this->getAccountBalances($gymId, '6');

        $totalRevenue = $this->sumBalances($revenue);
        $totalExpenses = $this->sumBalances($expenses);
        $totalCosts = $this->sumBalances($costs);

        $netIncome = $totalRevenue - ($totalExpenses + $totalCosts);

        $this->view('accounting/report_income', [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'costs' => $costs,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_costs' => $totalCosts,
            'net_income' => $netIncome
        ]);
    }

    private function getAccountBalances($gymId, $classPrefix) {
        // Sumar débitos y créditos por cuenta que empiece con el prefijo
        // Naturaleza:
        // 1 (Activo), 5 (Gasto), 6 (Costo) -> Saldo = Débito - Crédito
        // 2 (Pasivo), 3 (Patrimonio), 4 (Ingreso) -> Saldo = Crédito - Débito

        $isDebitNature = in_array($classPrefix, ['1', '5', '6', '7']);

        $sql = "
            SELECT a.code, a.name,
                   SUM(l.debit) as total_debit,
                   SUM(l.credit) as total_credit
            FROM gl_accounts a
            LEFT JOIN journal_lines l ON a.id = l.account_id
            LEFT JOIN journal_entries e ON l.entry_id = e.id
            WHERE a.gym_id = :gym_id
              AND a.code LIKE :prefix
              AND (e.status = 'posted' OR e.status IS NULL)
            GROUP BY a.id, a.code, a.name
            HAVING (SUM(l.debit) > 0 OR SUM(l.credit) > 0)
            ORDER BY a.code ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gym_id' => $gymId, ':prefix' => $classPrefix . '%']);
        $accounts = $stmt->fetchAll();

        foreach ($accounts as &$account) {
            if ($isDebitNature) {
                $account['balance'] = $account['total_debit'] - $account['total_credit'];
            } else {
                $account['balance'] = $account['total_credit'] - $account['total_debit'];
            }
        }

        return $accounts;
    }

    private function sumBalances($accounts) {
        $sum = 0;
        foreach ($accounts as $acc) {
            $sum += $acc['balance'];
        }
        return $sum;
    }
}
