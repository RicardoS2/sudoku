<?php
    // Inicializar grid e fixos
    $grid      = $_POST['cell'] ?? array_fill(0, 9, array_fill(0, 9, ''));
    $fixos     = isset($_POST['fixos']) ? json_decode($_POST['fixos'], true) : array_fill(0, 9, array_fill(0, 9, false));
    $resultado = '';
    $aviso     = '';

    // Função para validar linhas, colunas e blocos
    function validarSudoku($grid)
    {
        for ($i = 0; $i < 9; $i++) {
            $linha  = array_filter($grid[$i]);
            $coluna = array_filter(array_column($grid, $i));
            if (count($linha) !== count(array_unique($linha))) {
                return false;
            }

            if (count($coluna) !== count(array_unique($coluna))) {
                return false;
            }

        }
        for ($bi = 0; $bi < 9; $bi += 3) {
            for ($bj = 0; $bj < 9; $bj += 3) {
                $bloco = [];
                for ($i = 0; $i < 3; $i++) {
                    for ($j = 0; $j < 3; $j++) {
                        if ($grid[$bi + $i][$bj + $j] !== '') {
                            $bloco[] = $grid[$bi + $i][$bj + $j];
                        }
                    }
                }

                if (count($bloco) !== count(array_unique($bloco))) {
                    return false;
                }

            }
        }
        return true;
    }

    // Função para gerar números iniciais do Sudoku
    function gerarSudoku(&$grid, &$fixos, $quantidade = 20)
    {
        $grid        = array_fill(0, 9, array_fill(0, 9, ''));
        $fixos       = array_fill(0, 9, array_fill(0, 9, false));
        $preenchidos = 0;
        while ($preenchidos < $quantidade) {
            $i = rand(0, 8);
            $j = rand(0, 8);
            $n = rand(1, 9);
            if ($grid[$i][$j] === '') {
                $grid[$i][$j] = $n;
                if (validarSudoku($grid)) {
                    $fixos[$i][$j] = true;
                    $preenchidos++;
                } else {
                    $grid[$i][$j] = '';
                }
            }
        }
    }

    // Processar ação do formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'validar') {
            foreach ($grid as $linha) {
                if (in_array('', $linha)) {
                    $aviso = "Preencha todos os campos antes de validar!";
                    break;
                }
            }
            if (! $aviso) {
                $resultado = validarSudoku($grid) ? "O Sudoku é válido!" : "O Sudoku está incorreto!";
            }
        } elseif ($_POST['action'] === 'limpar') {
            gerarSudoku($grid, $fixos, 20);
        }
    } else {
        gerarSudoku($grid, $fixos, 20);
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sudoku</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex flex-col items-center justify-center min-h-screen p-4 text-white">
<h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6 text-blue-400 text-center">Sudoku</h1>

<?php if ($aviso): ?>
<div class="mb-4 p-3 rounded bg-blue-200 text-gray-900 text-center"><?php echo $aviso ?></div>
<?php endif; ?>

<?php if ($resultado): ?>
<div class="mb-4 p-3 rounded<?php echo strpos($resultado, 'válido') !== false ? 'bg-gray-700 text-blue-200' : 'bg-gray-800 text-red-400' ?> text-center"><?php echo $resultado ?></div>
<?php endif; ?>

<form method="post" class="flex flex-col items-center w-full max-w-md">
<input type="hidden" name="fixos" value="<?php echo htmlspecialchars(json_encode($fixos)) ?>">

<table class="border-collapse border-4 border-gray-700 w-full table-auto">
<?php for ($i = 0; $i < 9; $i++): ?>
<tr>
<?php for ($j = 0; $j < 9; $j++):
        $readonly = $fixos[$i][$j] ? 'readonly class="bg-gray-500 text-white w-10 h-10 sm:w-12 sm:h-12 text-center font-bold cursor-not-allowed"' :
        'class="' . (((int) ($i / 3) + (int) ($j / 3)) % 2 === 0 ? 'bg-gray-800' : 'bg-gray-700') . ' text-blue-300 w-10 h-10 sm:w-12 sm:h-12 text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"';
    ?>
			<td class="border border-gray-600">
			<input type="text" maxlength="1" name="cell[<?php echo $i ?>][<?php echo $j ?>]" value="<?php echo htmlspecialchars($grid[$i][$j]) ?>"<?php echo $readonly ?> oninput="this.value=this.value.replace(/[^1-9]/g,'')"/>
			</td>
			<?php endfor; ?>
</tr>
<?php endfor; ?>
</table>

<div class="flex flex-col sm:flex-row gap-4 mt-4 w-full justify-center">
<button type="submit" name="action" value="validar" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-500 transition text-white w-full sm:w-auto">Validar</button>
<button type="submit" name="action" value="limpar" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600 transition text-white w-full sm:w-auto">Limpar</button>
</div>
</form>
</body>
</html>
