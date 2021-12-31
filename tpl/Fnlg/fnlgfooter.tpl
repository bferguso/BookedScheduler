<style>
    .footer {
        min-height: 20px;
        height: 20px;
    }
</style>
<div class="container-fluid">
    <div class="navleft col-md-8 text-left">
        Logged in as: {ServiceLocator::GetServer()->GetUserSession()->FirstName} {ServiceLocator::GetServer()->GetUserSession()->LastName}
        ({ServiceLocator::GetServer()->GetUserSession()->Username})
        | {ServiceLocator::GetServer()->GetUserSession()->Email}
        | {ServiceLocator::GetServer()->GetUserSession()->Organization}
    </div>
    <div class="col-md-4 text-right">
        Version: {Configuration::Instance()->GetKey("fnlg.instance")} {Configuration::Instance()->GetKey("fnlg.version")} ({Configuration::Instance()->GetKey("fnlg.deployment.number")})
    </div>
</div>