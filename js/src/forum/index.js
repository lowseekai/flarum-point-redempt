import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import LinkButton from 'flarum/common/components/LinkButton';
import PointRedemptionPage from './components/PointRedemptionPage';

app.initializers.add('lowseekai/flarum-point-redempt', () => {
  app.routes.pointRedemption = { path: '/point-redemption', component: PointRedemptionPage };
  extend(IndexSidebar.prototype, 'navItems', function (items) {
    if (!app.session.user || !app.forum.attribute('canRedeemPoints')) return;
    items.add('pointRedemption', <LinkButton href={app.route('pointRedemption')} icon="fas fa-gift">{app.translator.trans('lowseekai-point-redempt.forum.title')}</LinkButton>, 83);
  });
});
