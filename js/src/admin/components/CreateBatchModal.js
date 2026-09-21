import app from 'flarum/admin/app';
import Button from 'flarum/common/components/Button';
import Modal from 'flarum/common/components/Modal';

function localDateTime(offsetDays = 0, offsetMinutes = 0) {
  const date = new Date(Date.now() + offsetDays * 86400000 + offsetMinutes * 60000);
  const offset = date.getTimezoneOffset();
  const local = new Date(date.getTime() - offset * 60000);

  return local.toISOString().slice(0, 16);
}

export default class CreateBatchModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);
    this.name = '';
    this.pointsAmount = 100;
    this.quantity = 10;
    this.startsAt = localDateTime(0, 5);
    this.expiresAt = localDateTime(30);
    this.note = '';
    this.codes = null;
    this.batch = null;
    this.loading = false;
    this.error = null;
  }

  className() {
    return 'Modal--large PointRedemptionCreateBatchModal';
  }

  title() {
    return app.translator.trans(
      `lowseekai-point-redempt.admin.${this.codes ? 'generated_title' : 'create_title'}`
    );
  }

  content() {
    return this.codes ? this.resultContent() : this.formContent();
  }

  formContent() {
    return (
      <div className="Modal-body">
        <form className="Form" onsubmit={this.submit.bind(this)}>
          {this.field('name', 'text', 'admin.fields.name', this.name, (value) => (this.name = value), true)}
          {this.field(
            'pointsAmount',
            'number',
            'admin.fields.points_amount',
            this.pointsAmount,
            (value) => (this.pointsAmount = value),
            true,
            1,
            1000000
          )}
          {this.field(
            'quantity',
            'number',
            'admin.fields.quantity',
            this.quantity,
            (value) => (this.quantity = value),
            true,
            1,
            1000
          )}
          {this.field(
            'startsAt',
            'datetime-local',
            'admin.fields.starts_at',
            this.startsAt,
            (value) => (this.startsAt = value),
            true
          )}
          {this.field(
            'expiresAt',
            'datetime-local',
            'admin.fields.expires_at',
            this.expiresAt,
            (value) => (this.expiresAt = value),
            true
          )}
          <div className="Form-group">
            <label for="point-redemption-note">{app.translator.trans('lowseekai-point-redempt.admin.fields.note')}</label>
            <textarea
              id="point-redemption-note"
              className="FormControl"
              maxlength="500"
              value={this.note}
              oninput={(event) => (this.note = event.target.value)}
            />
          </div>
          {this.error && <div className="PointRedemptionAdmin-error">{this.error}</div>}
          <div className="Form-group Form-controls">
            <Button type="submit" className="Button Button--primary" icon="fas fa-key" loading={this.loading} disabled={this.loading}>
              {app.translator.trans('lowseekai-point-redempt.admin.generate')}
            </Button>
            <Button type="button" className="Button" icon="fas fa-times" onclick={this.hide.bind(this)}>
              {app.translator.trans('lowseekai-point-redempt.admin.cancel')}
            </Button>
          </div>
        </form>
      </div>
    );
  }

  field(key, type, labelKey, value, onchange, required = false, min = null, max = null) {
    const id = `point-redemption-${key}`;

    return (
      <div className="Form-group">
        <label for={id}>{app.translator.trans(`lowseekai-point-redempt.${labelKey}`)}</label>
        <input
          id={id}
          className="FormControl"
          type={type}
          value={value}
          required={required}
          min={min}
          max={max}
          oninput={(event) => onchange(type === 'number' ? Number(event.target.value) : event.target.value)}
        />
      </div>
    );
  }

  async submit(event) {
    event.preventDefault();
    this.loading = true;
    this.error = null;
    m.redraw();

    try {
      const response = await app.request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/point-redemption/batches',
        body: {
          data: {
            attributes: {
              name: this.name,
              pointsAmount: this.pointsAmount,
              quantity: this.quantity,
              startsAt: new Date(this.startsAt).toISOString(),
              expiresAt: new Date(this.expiresAt).toISOString(),
              note: this.note,
            },
          },
        },
      });

      this.batch = response.data;
      this.codes = response.meta?.codes || [];
      this.attrs.onCreated?.(this.batch);
    } catch (error) {
      this.error = this.errorMessage(error);
    } finally {
      this.loading = false;
      m.redraw();
    }
  }

  resultContent() {
    const codes = this.codes || [];

    return (
      <div className="Modal-body">
        <p>{app.translator.trans('lowseekai-point-redempt.admin.generated_help', { count: codes.length })}</p>
        <textarea className="FormControl PointRedemptionCreateBatchModal-codes" readonly value={codes.join('\n')} />
        <div className="Form-controls PointRedemptionCreateBatchModal-actions">
          <Button className="Button Button--primary" icon="fas fa-download" onclick={this.download.bind(this)}>
            {app.translator.trans('lowseekai-point-redempt.admin.download_csv')}
          </Button>
          <Button className="Button" icon="fas fa-times" onclick={this.hide.bind(this)}>
            {app.translator.trans('lowseekai-point-redempt.admin.close')}
          </Button>
        </div>
      </div>
    );
  }

  download() {
    const csv = `code\n${(this.codes || []).join('\n')}\n`;
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = `point-redemption-${this.batch?.id || 'codes'}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  }

  errorMessage(error) {
    const detail = error?.response?.errors?.[0]?.detail || error?.response?.errors?.[0]?.title;

    return detail || error?.message || app.translator.trans('lowseekai-point-redempt.admin.error');
  }
}
