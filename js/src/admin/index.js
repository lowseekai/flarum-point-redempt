import app from 'flarum/admin/app';
import PointRedemptionAdminPage from './components/PointRedemptionAdminPage';

app.initializers.add('lowseekai/flarum-point-redempt', () => {
  app.registry.for('lowseekai-point-redempt')
    .registerSetting(
      {
        setting: 'point-redempt.display_name',
        type: 'text',
        label: app.translator.trans('lowseekai-point-redempt.admin.settings.display_name'),
        help: app.translator.trans('lowseekai-point-redempt.admin.settings.display_name_help'),
        default: '积分兑换',
      },
      30
    )
    .registerSetting(
      {
        setting: 'point-redempt.points_icon',
        type: 'text',
        label: app.translator.trans('lowseekai-point-redempt.admin.settings.points_icon'),
        help: app.translator.trans('lowseekai-point-redempt.admin.settings.points_icon_help'),
        default: 'fas fa-coins',
      },
      20
    )
    .registerSetting(
      {
        setting: 'point-redempt.get_code_url',
        type: 'text',
        label: app.translator.trans('lowseekai-point-redempt.admin.settings.get_code_url'),
        help: app.translator.trans('lowseekai-point-redempt.admin.settings.get_code_url_help'),
        default: '',
      },
      10
    )
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
