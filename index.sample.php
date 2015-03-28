        <?php
        include './observcheck.php';
        include './passw.php';
        $conn = new mysqli($host, $user, $password, $database);

        $model = new ObservcheckModel($conn);
        $presenter = new CSVPresenter();
        
        $model->RegisterChecker(new ComputationChecker());
        $model->RegisterChecker(new SunriseChecker());
        $model->RegisterChecker(new PositionChecker());
        
        $data = $model->Process();
        $presenter->Render($data);