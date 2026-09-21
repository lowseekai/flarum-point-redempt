import app from 'flarum/admin/app';
import PointRedemptionAdminPage from './components/PointRedemptionAdminPage';

app.initializers.add('lowseekai/flarum-point-redempt', () => {
  app.registry.for('lowseekai-point-redempt')
    .registerPage(PointRedemptionAdminPage)
    .registerPermission(
      {
        icon: 'fas fa-gift',
        label: app.translator.trans('lowseekai-point-redempt.admin.permissions.redeem'),
        permission: 'pointRedemption.redeem',
        allowGuest: false,
      },
      'view'
    )
    .registerPermission(
      {
        icon: 'fas fa-cog',
        label: app.translator.trans('lowseekai-point-redempt.admin.permissions.manage'),
        permission: 'pointRedemption.manage',
        allowGuest: false,
      },
      'moderate'
    );
});
