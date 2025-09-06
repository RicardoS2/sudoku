<?php
    class Sudoku
    {
        private array $grid;
        private ?string $resultado = null;
        private ?string $aviso     = null;

        public function __construct(array $grid = null)
        {
            $this->grid = $grid ?? array_fill(0, 9, array_fill(0, 9, ''));
        }

        public function getGrid(): array
        {
            return $this->grid;
        }

        public function getResultado(): ?string
        {
            return $this->resultado;
        }

        public function getAviso(): ?string
        {
            return $this->aviso;
        }

        private function validar(array $arr): bool
        {
            $arr = array_filter($arr);
            $arr = array_unique($arr);
            return count($arr) === 9;
        }

        private function validarLinhas(): bool
        {
            foreach ($this->grid as $linha) {
                if (! $this->validar($linha)) {
                    return false;
                }

            }
            return true;
        }

        private function validarColunas(): bool
        {
            for ($i = 0; $i < 9; $i++) {
                $coluna = [];
                for ($j = 0; $j < 9; $j++) {
                    $coluna[] = $this->grid[$j][$i];
                }
                if (! $this->validar($coluna)) {
                    return false;
                }

            }
            return true;
        }

        private function validarBlocos(): bool
        {
            for ($i = 0; $i < 9; $i += 3) {
                for ($j = 0; $j < 9; $j += 3) {
                    $bloco = [];
                    for ($k = 0; $k < 3; $k++) {
                        for ($l = 0; $l < 3; $l++) {
                            $bloco[] = $this->grid[$i + $k][$j + $l];
                        }
                    }

                    if (! $this->validar($bloco)) {
                        return false;
                    }

                }
            }
            return true;
        }

        public function validarSudoku(): bool
        {
            return $this->validarLinhas() && $this->validarColunas() && $this->validarBlocos();
        }

        public function processAction(string $action): void
        {
            if ($action === 'validar') {
                foreach ($this->grid as $linha) {
                    if (in_array('', $linha)) {
                        $this->aviso = "Preencha todos os campos antes de validar!";
                        return;
                    }
                }
                $this->resultado = $this->validarSudoku() ? "O Sudoku é válido!" : "O Sudoku está incorreto!";
            } elseif ($action === 'limpar') {
                $this->grid = array_fill(0, 9, array_fill(0, 9, ''));
            }
        }
    }

    $sudoku = new Sudoku($_POST['cell'] ?? null);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $sudoku->processAction($_POST['action']);
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Rick-Sudoku</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex flex-col items-center justify-center min-h-screen p-4 text-white">

<h1 class="text-3xl font-bold mb-6 text-blue-400">Validador de Sudoku</h1>

<?php if ($sudoku->getAviso()): ?>
    <div class="mb-4 p-3 rounded bg-blue-200 text-gray-900"><?php echo $sudoku->getAviso(); ?></div>
<?php endif; ?>

<?php if ($sudoku->getResultado()): ?>
    <div class="mb-4 p-3 rounded<?php echo strpos($sudoku->getResultado(), 'válido') !== false ? 'bg-gray-700 text-blue-200' : 'bg-gray-800 text-red-400'; ?>">
        <?php echo $sudoku->getResultado(); ?>
    </div>
<?php endif; ?>

<form method="post" class="flex flex-col items-center">
    <table class="border-collapse border-4 border-gray-700">
        <?php $grid = $sudoku->getGrid(); ?>
<?php for ($i = 0; $i < 9; $i++): ?>
        <tr>
            <?php for ($j = 0; $j < 9; $j++):
                    $blockColor = ((int) ($i / 3) + (int) ($j / 3)) % 2 === 0 ? 'bg-gray-800' : 'bg-gray-700';
                ?>
		            <td class="border border-gray-600">
		                <input type="text" maxlength="1" name="cell[<?php echo $i ?>][<?php echo $j ?>]"
		                       value="<?php echo htmlspecialchars($grid[$i][$j]) ?>"
		                       class="w-12 h-12 text-center font-bold		                                                             	                                                              <?php echo $blockColor ?> text-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
		                       oninput="this.value=this.value.replace(/[^1-9]/g,'')"/>
		            </td>
		            <?php endfor; ?>
        </tr>
        <?php endfor; ?>
    </table>

    <div class="flex gap-4 mt-4">
        <button type="submit" name="action" value="validar" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-500 transition text-white">Validar</button>
        <button type="submit" name="action" value="limpar" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600 transition text-white">Limpar</button>
    </div>
</form>
</body>
</html>
