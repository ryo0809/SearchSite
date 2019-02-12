<html>
    <head>
        <meta charset="utf-8">
        <link href="/css/SearchPortal.css" rel="stylesheet" type="text/css"> 
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css" integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">
        <title>サイト検索</title>
    </head>
    <body>
    @php
        function post($url, $data = array()) {
                $stream = stream_context_create(array('http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content'   => http_build_query($data),
                )));
            
                return file_get_contents($url, false, $stream);
        }
        
        if (isset($_GET['search'])) {
            $key = $_GET['search'];
            $csvText = array_column($datas, 2);
            $csvList = [];
            foreach ($csvText as $text) {
                if (is_null($key) || $key === '') {
                    continue;
                }
                if (mb_strpos($text, $key) !== false) {
                    $keys = array_keys($csvText,$text);
                    $filter = implode($keys);
                    $csvList[] = $datas[$filter];
                }
            }
            $countObj = count($csvList);
        }
        $sortList = array();
    @endphp
        <div class="title">
            北海道情報大学 サイト検索
        </div>
        <form class="form-group" id="form" action="" method="get">
            <input class="form-control" type="text" name="search" placeholder="キーワードを入力">
        </form>
        @if (!empty($csvList))
            <p class="result">{{'検索結果：'.$countObj.'件のリンクが見つかりました。'}}</p>
            @foreach($csvList as $data)
                @php
                    $url = 'https://labs.goo.ne.jp/api/morph';        
                    $param = array(
                        'app_id'    => '021b5ea96651b1c5184437f642a8c20f844232741ae4db82a2cf6f241b8103fa',
                        'sentence'  => $data[2],
                        'pos_filter' => '名詞'
                    );
                    $json = post($url, $param);
                    $obj = json_decode($json);
                    $wordList = array();
                    $sum = 0;
                    $matchText = array();
                    foreach ($obj->word_list as $word) {
                        $count = count($word);
                        $sum += count($word);                       
                        for ($i=0;$i<=$count;$i++) {
                            $wordList[] = array_column($word, 0);
                            $matchText[] = array_count_values(array_column($word, 0));
                            break;
                        }
                    }
                    $sumText = array_sum(array_column($matchText, $key));
                    $tf = $sumText / $sum;
                    $sumUrl = count($datas);
                    $idf = log($sumUrl / $countObj) + 1;
                    $tfIdf = $tf * $idf;
                    array_push($data, round($tfIdf, 3));
                    array_push($sortList, $data);
                @endphp
            @endforeach
            @php 
                foreach ($sortList as $key => $value) {
                    $sort[$key] = $value[3];
                }
                array_multisort($sort, SORT_DESC, SORT_NUMERIC, $sortList); 
            @endphp
            @foreach ($sortList as $data)
                <div class="content">
                    <p class="caption">{{$data[0]}}</p>
                    <div class="link">
                        <a href={{$data[1]}} target='_blank'><p>{{$data[1]}}</p></a>
                    </div>
                    <p class="text">{{'：'.$data[2]}}</p>
                </div>                
            @endforeach
        @else
            @if (empty($datas['param']))
                <ul class="description">
                    <li>北海道情報大学に関連したサイト情報を検索出来ます</li>
                    <li>キーワード(単語)を入力してEnterキーで検索して下さい</li>
                    <li>検索結果が見つからない場合にはキーワードを変更して下さい</li>
                </ul>
            @else
                <div class="exception">
                    <p>検索結果が見つかりませんでした。</p>
                    <p>検索のヒント</p>
                    <ul>
                        <li>キーワードを変更</li>
                        <li>北海道情大学に関連したキーワードであるか</li>
                        <li>誤字、脱字を確認</li>
                    </ul>
                </div>
            @endif
        @endif
    </body>
</html>
