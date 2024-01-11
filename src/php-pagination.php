<?php
$handle = fopen('Patents.json', 'r');
$json = stream_get_contents($handle);
fclose($handle);
$dataArray = json_decode($json, true);

$varietiesPerPage = 25;
$page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) : 1;
$startIndex = ($page - 1) * $varietiesPerPage;
$endIndex = $startIndex + $varietiesPerPage - 1;

$executed = false;
$filtered = false;

if (!$executed) {
  $varieties = $dataArray['Varieties'];

  $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
  $totalvarieties = count($dataArray['Varieties']);
  $totalPages = ceil($totalvarieties / $varietiesPerPage);
  $executed = true;
};



$searchQuery = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : '';
if ($searchQuery) {
  $varieties = [];
  $counter = 0;
  $loweredQuery = mb_strtolower($searchQuery);
  $loweredQuery = str_replace('&quot;', '', $loweredQuery);
  $loweredQuery = trim($loweredQuery);
  foreach ($dataArray['Varieties'] as $variety) {
    $kindName = mb_strtolower($variety['Kind']);
    $varietyName = mb_strtolower($variety['Name']);
    $ownersName = str_replace("'", '', $variety['Owners']);
    $ownersName = mb_strtolower(htmlspecialchars($ownersName, ENT_QUOTES, 'UTF-8'));
    $patentName = mb_strtolower($variety['Patent']);
    $closedName = mb_strtolower($variety['Closed']);
    if (
      strpos($kindName, $loweredQuery) !== false ||
      (!is_numeric($loweredQuery) && strpos($varietyName, $loweredQuery) !== false) ||
      (!is_numeric($loweredQuery) && strpos($ownersName, $loweredQuery) !== false) ||
      ($patentName === $loweredQuery) !== false ||
      ($closedName === $loweredQuery) !== false
    ) {
      $varieties[] = $variety;
      $counter++;
    }
  }
  $varieties = array_slice($varieties, $startIndex, $varietiesPerPage);
  $totalPages = ceil($counter / $varietiesPerPage);
  $filtered = true;
}

if (isset($_POST['refreshButton'])) {
  $varieties = array_slice($dataArray['Varieties'], $startIndex, $varietiesPerPage);
  $totalvarieties = count($dataArray['Varieties']);
  $totalPages = ceil($totalvarieties / $varietiesPerPage);
  $filtered = false;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <title>Патенты утратившие силу</title>
  <style type="text/css">
    /* style-kit in server => style only for local dev*/
    .patents__table {
      margin: auto;
      width: 80%;
      border-collapse: collapse;
    }

    .patents__table__row:nth-child(even) {
      background-color: #f2f2f2;
    }

    .patents__table__header {
      font-weight: bold;
      padding: 8px;
      text-align: left;
    }

    .patents__table__cell {
      padding: 8px;
      margin: 20px;
      max-width: 500px;
      text-align: left;
      line-height: 1.5;
    }

    .patents__pagination {
      margin-top: 16px;
    }

    .patents__pagination__link {
      margin-right: 8px;
      text-decoration: none;
      color: blue;
    }

    .patents__pagination__link_inactive {
      color: green;
    }

    .patents__pagination__link:hover {
      text-decoration: underline;
    }

    .patents__forms {
      display: flex;
      justify-content: center;
    }

    .patents__form {
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 5px;
      height: 50px;
      margin: 15px 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .patents__form__refresh {
      border: none;
      justify-content: center;
    }

    .patents__form__input {
      border: none;
      outline: none;
      padding: 10px;
      border-radius: 0;
      height: 45px;
      margin: 0 0 0 10px;
      padding: 0;
      width: 100%;
      font-family: Inter;
    }

    .patents__form__label {
      width: max-content;
      white-space: nowrap;
      font-size: 12px;
    }

    .patents__filtered-text {
      color: #4B75B4;
      padding: 10px 0;
    }

    .patents__form__button {
      appearance: none;
      background-color: #FAFBFC;
      border: 1px solid rgba(27, 31, 35, 0.15);
      border-radius: 6px;
      box-shadow: rgba(27, 31, 35, 0.04) 0 1px 0, rgba(255, 255, 255, 0.25) 0 1px 0 inset;
      box-sizing: border-box;
      color: #24292E;
      cursor: pointer;
      display: inline-block;
      font-family: -apple-system, system-ui, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
      font-size: 14px;
      font-weight: 500;
      line-height: 20px;
      list-style: none;
      padding: 6px 16px;
      position: relative;
      transition: background-color 0.2s cubic-bezier(0.3, 0, 0.5, 1);
      user-select: none;
      -webkit-user-select: none;
      touch-action: manipulation;
      vertical-align: middle;
      white-space: nowrap;
      word-wrap: break-word;
    }

    .patents__form__button:hover {
      background-color: #F3F4F6;
      text-decoration: none;
      transition-duration: 0.1s;
    }

    .patents__form__button:disabled {
      background-color: #FAFBFC;
      border-color: rgba(27, 31, 35, 0.15);
      color: #959DA5;
      cursor: default;
    }

    .patents__form__button:active {
      background-color: #EDEFF2;
      box-shadow: rgba(225, 228, 232, 0.2) 0 1px 0 inset;
      transition: none 0s;
    }

    .patents__form__button:focus {
      outline: 1px transparent;
    }

    .patents__form__button:before {
      display: none;
    }

    .patents__form__button:-webkit-details-marker {
      display: none;
    }

    .patents__form__button-refresh {
      align-self: center;
      height: 50px;
    }

    .patents__header {
      text-align: center;
      margin: 20px;
    }

    .patents__form__search {
      width: 100%;
    }

    @media (max-width: 768px) {
      .patents__table__header {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }

      .patents__table__cell {
        margin: 10px;
        font-size: 10px;
        padding: 2px;
      }

      .patents__form__label {
        display: none;
      }

      .patents__forms {
        flex-direction: column;
      }
    }

    @media (max-width: 480px) {
      .patents__table__cell {
        margin: 5px;
      }

      .patents__pagination {
        margin-top: 8px;
      }
    }
  </style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <div class="content">
        <div class="patents__forms">
      <form method="GET" action="" class="patents__form patents__form__search">
        <label class="patents__form__label" for="patents__searchInput">Поиск:</label>
        <input class="patents__form__input" id="patents__searchInput" name="search" placeholder="Введите запрос" list="patents__searchOptions" value="<?php echo is_array($searchQuery) ? implode("\n", $searchQuery) : $searchQuery; ?>">
        <datalist class="patents__form__datalist" id="patents__searchOptions">
        <?php
        $selectOptions = [];
        $properties = ['Kind', 'Name', 'Owners'];

        foreach ($dataArray['Varieties'] as $variety) {
          foreach ($properties as $property) {
            $propertyName = $variety[$property];
            if ($property === 'Owners') {
              $owners = explode(';', $propertyName);
              foreach ($owners as $owner) {
                $ownerName = str_replace("'", '', trim($owner));
                if (!in_array($ownerName, $selectOptions)) {
                  $selectOptions[] = $ownerName;
                  echo "<option value=\"$ownerName\">$ownerName</option>";
                }
              }
            } else {
              if (!in_array($propertyName, $selectOptions)) {
                $selectOptions[] = $propertyName;
                echo "<option value=\"$propertyName\">$propertyName</option>";
              }
            }
          }
        }
        ?>
        </datalist>
        <button class="patents__form__button" type="submit">&#128270;</button>
      </form>


      <form class="patents__form patents__form__refresh" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <button class="patents__form__button patents__form__button-refresh" name="refreshButton" type="submit"> &#11148; </button>
      </form>

    </div>

    <?php
    if ($filtered) {
      echo '<span class="patents__filtered-text">Отфильтрованный список</span>';
      echo '<br />';
    }
    echo '<table class="patents__table">';
    echo '<tr class="patents__table__row">';
    echo '<th class="patents__table__header"><b>Род и вид</b></th>';
    echo '<th class="patents__table__header"><b>Сорт</b></th>';
    echo '<th class="patents__table__header"><b>Патент</b></th>';
    echo '<th class="patents__table__header"><b>Окончание действия</b></th>';
    echo '<th class="patents__table__header"><b>Патентообладатели</b></th>';
    echo '</tr>';
    foreach ($varieties as $variety) {
      echo '<tr class="patents__table__row">';
      echo '<td class="patents__table__cell">' . $variety['Kind'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Name'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Patent'] . '</td>';
      echo '<td class="patents__table__cell">' . $variety['Closed'] . '</td>';
      $owners = $variety['Owners'];
      $ownersFormatted = str_replace(';', '<br/>-  ', $owners);
      $ownersFormatted = str_replace("'", '"', $ownersFormatted);
      echo '<td class="patents__table__cell">- ' . $ownersFormatted . '</td>';
      echo '</tr>';
    }
    echo '</table>';
    if ($filtered) {
      echo '<ul class="pagination patents__pagination">';
      for ($i = $page - 5; $i <= $page + 5 && $i <= $totalPages; $i++) {
        if ($i > 0) {
          $isActivePage = ($i == $page);
          $queryParam = '&search=' . $searchQuery;

          echo ($isActivePage ?
            '<li><a class="patents__pagination__link pagination__link_inactive">' . $i . '</a></li>'
            :
            '<li><a class="patents__pagination__link" href="?page=' . $i . $queryParam . '">' . $i . '</a></li>');

          if ($i == $page + 5 && $i < $totalPages) {
            echo "...";
            echo '<li><a class="patents__pagination__link" href="?page=' . $totalPages . $queryParam . '">' . $totalPages . '</a></li>';
          }
        }
      }
      echo '</ul>';
    } else {
      echo '<ul class="pagination patents__pagination">';
      for ($i = $page - 5; $i <= $page + 5 && $i <= $totalPages; $i++) {
        if ($i > 0) {
          if ($i == $page) {
            echo '<li><a class="patents__pagination__link pagination__link_inactive">' . $i . '</a></li>';
          } else {
            echo '<li><a class="patents__pagination__link" href="?page=' . $i . '">' . $i . '</a></li>';
          }
        }
        if ($i == $page + 5 && $i < $totalPages) {
          echo "...";
          echo '<li><a class="patents__pagination__link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }
      }
    }
    ?>
  </div>
  <script>
    const selectOptions = <?php echo json_encode($selectOptions); ?>;
    const query = <?php echo isset($loweredQuery) ? json_encode(strval($searchQuery)) : "''"; ?>;
  </script>
  <script src="index.js"></script>
</body>

</html>