<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property int $id
 * @property string|null $file_location
 * @property string|null $original_file_name
 * @property int|null $version
 * @property int|null $file_size
 * @property string|null $date_created
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'file_size'], 'integer'],
            [['date_created'], 'safe'],
            [['file_location', 'original_file_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_location' => 'File Location',
            'original_file_name' => 'Original File Name',
            'version' => 'Version',
            'file_size' => 'File Size',
            'date_created' => 'Date Created',
        ];
    }
}
