<div class="frm_wrap">
    <?php
    FrmAppHelper::get_admin_header(
        array(
            'label' => $params['template'] ? __('Templates', 'formidable') : __('Statistic', 'formidable'),
        )
    );
    ?>
    <style>
        span[color=red] {
            color: red;
            font-weight: bold;
        }
    </style>
    <div class="wrap">
        <?php
        require(FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php');
        ?>

        <h1 id="frm_form_heading"><?= $form->name ?></h1>

        <div class="frm_form_fields">
            <div class="postbox">

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                        <?php foreach ($fields as $field) : ?>
                            <tr>
                                <th scope="row"><?= $field['name'] ?>:</th>
                                <td style="display: block;max-height: 150px;overflow-y: scroll;">
                                    <?php
                                    switch ($field['type']) {
                                        case 'checkbox':
                                        case 'select':
                                        case 'radio':
                                            {
                                                foreach ($field['options'] as $option) {

                                                    ?>
                                                    <div>
                                                        <?= $option['label'] ?> -
                                                        <span color="red"> <?= $option['percent'] . '%' ?></span>
                                                    </div>

                                                    <?php
                                                }
                                                break;
                                            }
                                        default:
//                                        case 'text':
                                            {
                                                foreach ($field['options'] as $option) {

                                                    ?>
                                                    <div>
                                                        <?= $option['name'] ?>
                                                    </div>

                                                    <?php
                                                }
                                                break;
                                            }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>
