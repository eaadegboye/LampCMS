<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is licensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 *       the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attributes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2012 (or current year) Dmitri Snytkine
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms\Image;

use Lampcms\FS\Path;
use Lampcms\AccessException;

class ImageUploadParser
{

    protected $User;

    protected $Registry;

    protected $EditorOptions;

    protected $file;

    public function __construct(\Lampcms\Registry $Registry, \Lampcms\User $User, $file)
    {
        $this->Registry      = $Registry;
        $this->User          = $User;
        $this->file          = $file;
        $this->EditorOptions = $Registry->Ini->getSection('EDITOR');
    }


    public function parse()
    {

        return $this->checkPermission()
            ->checkUploadFlood()
            ->checkFileSize()
            ->resize();

    }


    protected function checkPermission()
    {

        if (empty($this->EditorOptions['IMAGE_UPLOAD_FILE_SIZE'])) {
            throw new AccessException('@@Image upload not allowed@@');
        }

        $Acl = $this->Registry->Acl;

        if (!$Acl->isAllowed($this->User->getRoleId(), null, 'upload_image')) {
            throw new AccessException('@@Your account does not have permission to upload images@@');
        }

        $minReputation = (int)$this->EditorOptions['IMAGE_UPLOAD_MIN_REPUTATION'];
        $rep           = $this->User->getReputation();

        if ($rep < $minReputation) {
            throw new AccessException($this->Registry->Tr->get('Minimum reputation of {min} is required to upload images. Your reputation is {reputation}',
                array('{min}' => $minReputation, '{rep}' => $rep)));
        }

        return $this;
    }


    /**
     * @todo check difference in seconds between now and previous file upload for
     *       this user. Use MIN_IMAGE_UPLOAD_INTERVAL setting
     * @return ImageUploadParser
     */
    protected function checkUploadFlood()
    {

        return $this;
    }


    protected function checkFileSize()
    {

        $fileSize = @filesize($this->file);
        if ($fileSize > (1024 * 1024 * $this->EditorOptions['IMAGE_UPLOAD_FILE_SIZE'])) {
            $res = @unlink($this->file);
            d('Uploaded size too large. Deleted: ' . $res);
            throw new AccessException($this->Registry->Tr->get('Uploaded file is too large. Maximum size allowed in {max} MB',
                array('{max}' => $this->EditorOptions['IMAGE_UPLOAD_FILE_SIZE'])));
        }

        return $this;
    }


    protected function resize()
    {

        $Editor = \Lampcms\Image\Editor::factory($this->Registry);
        $Editor->loadImage($this->file);
        $basePath = LAMPCMS_DATA_DIR . 'img';

        $fileName = $this->User->getUid() . '_' . time();
        $fileExt  = $Editor->getExtension();

        $destFolder = Path::prepareByTimestamp($basePath, false);
        $origPath   = $destFolder . $fileName . $fileExt;
        if (!copy($this->file, LAMPCMS_DATA_DIR . 'img' . DIRECTORY_SEPARATOR . $origPath)) {
            throw new \Lampcms\DevException('Unable to copy orig file from ' . $this->file . ' to ' . LAMPCMS_DATA_DIR . 'img' . DIRECTORY_SEPARATOR . $origPath);
        }

        /**
         * @todo update USER collection to insert i_last_upload_ts timestamp
         * will be used to check upload flood
         */
        return \str_replace(DIRECTORY_SEPARATOR, '/', $origPath);
    }
}