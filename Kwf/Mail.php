<?php
class Kwf_Mail extends Zend_Mail
{
    protected $_attachImages = false;
    protected $_domain = null;

    public function __construct($mustNotBeSet = null)
    {
        if ($mustNotBeSet) {
            throw new Kwf_Exception("Kwf_Mail got replaced with Kwf_Mail_Template");
        }
        parent::__construct('utf-8');
    }

    public function getMailContent($type = Kwf_Model_Mail_Row::MAIL_CONTENT_AUTO)
    {
        if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_AUTO) {
            $ret = $this->getBodyHtml(true);
            if (!$ret) $ret = $this->getBodyText(true);
            return $ret;
        } else if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_HTML) {
            return $this->getBodyHtml(true);
        } else if ($type == Kwf_Model_Mail_Row::MAIL_CONTENT_TEXT) {
            return $this->getBodyText(true);
        }
        return null;
    }

    public function addCc($email, $name='')
    {
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            if ($name) {
                $this->addHeader('X-Real-Cc', $name ." <".$email.">");
            } else {
                $this->addHeader('X-Real-Cc', $email);
            }
        } else {
            parent::addCc($email, $name);
        }
        return $this;
    }

    public function addBcc($email)
    {
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            $this->addHeader('X-Real-Bcc', $email);
        } else {
            parent::addBcc($email);
        }
        return $this;
    }

    public function addTo($email, $name='')
    {
        if (is_array($email)) $email = implode(';', $email);
        $this->_ownTo[] = trim("$name <$email>");
        if (Kwf_Registry::get('config')->debug->sendAllMailsTo) {
            if ($name) {
                $this->addHeader('X-Real-Recipient', $name ." <".$email.">");
            } else {
                $this->addHeader('X-Real-Recipient', $email);
            }
        } else {
            if (strpos($email, ';') !== false) $email = explode(';', $email);
            parent::addTo($email, $name);
        }
        return $this;
    }

    public function setAttachImages($attachImages)
    {
        $this->_attachImages = $attachImages;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->_domain = $domain;
        return $this;
    }

    public function getDomain()
    {
        if (!$this->_domain) {
            $this->_domain = Kwf_Config::getValue('server.domain');
        }
        return $this->_domain;
    }

    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        while (preg_match('/(img src|background)=\"\/(.*?)\"/i', $html, $matches)) {
            $path = '/' . $matches[2];
            if ($this->_attachImages) {
                if (substr($path, 0, 6) == '/media') {
                    $parts = explode('/', substr($path, 1));
                    $class = $parts[1];
                    $id = $parts[2];
                    $type = $parts[3];
                    $checksum = $parts[4];
                    $filename = $parts[6];
                    $output = Kwf_Media::getOutputWithoutCheckingIsValid($class, $id, $type);
                } else {
                    try {
                        $f = new Kwf_Assets_Loader();
                        $output = $f->getFileContents(substr($path, 8));
                    } catch (Kwf_Exception_NotFound $e) {
                        throw new Kwf_Exception('Asset not found: ' . $path);
                    }
                }
                if (isset($output['contents'])) {
                    $contents = $output['contents'];
                } else if (isset($output['file'])) {
                    $contents = file_get_contents($output['file']);
                } else {
                    throw new Kwf_Exception("didn't get image contents");
                }
                $image = new Zend_Mime_Part($contents);
                $image->type = $output['mimeType'];
                $image->disposition = Zend_Mime::DISPOSITION_INLINE;
                $image->encoding = Zend_Mime::ENCODING_BASE64;
                $filename = rawurldecode(substr(strrchr($path, '/'), 1));
                $filename = preg_replace('/([^a-z0-9\-\.]+)/i', '_', $filename);
                $image->filename = $filename;
                $image->id = md5($path);
                $this->setType(Zend_Mime::MULTIPART_RELATED);
                $this->addAttachment($image);
                $replace = "cid:{$image->id}";
            } else {
                $replace = "http://" . $this->getDomain() . $path;
            }
            $html = str_replace($matches[0], "{$matches[1]}=\"$replace\"", $html);
        }
        parent::setBodyHtml($html, $charset, $encoding);
    }

    public function setFrom($email, $name='')
    {
        if (empty($email)) {
            throw new Kwf_Exception("Email address '$email' cannot be set as from part in a mail. Empty or invalid address.");
        }
        parent::setFrom($email, $name);
        return $this;
    }

    public function send($transport = null)
    {
        $mailSendAll = Kwf_Registry::get('config')->debug->sendAllMailsTo;
        if ($mailSendAll) {
            parent::addTo($mailSendAll);
        }

        $mailSendAllBcc = Kwf_Registry::get('config')->debug->sendAllMailsBcc;
        if ($mailSendAllBcc) {
            parent::addBcc($mailSendAllBcc);
        }

        if ($this->getFrom() == null) {
            $sender = $this->getSenderFromConfig();
            $this->setFrom($sender['address'], $sender['name']);
        }

        if (!$transport) {
            if (Kwf_Config::getValue('email.smtp.host')) {
                $transport = new Zend_Mail_Transport_Smtp(
                    Kwf_Config::getValue('email.smtp.host'),
                    array(
                        'auth' => Kwf_Config::getValue('email.smtp.auth'),
                        'username' => Kwf_Config::getValue('email.smtp.username'),
                        'password' => Kwf_Config::getValue('email.smtp.password'),
                        'ssl' => Kwf_Config::getValue('email.smtp.ssl'),
                        'port' => Kwf_Config::getValue('email.smtp.port'),
                    )
                );
            } else {
                if ($this->getReturnPath()) {
                    $transport = new Zend_Mail_Transport_Sendmail('-f ' . $this->getReturnPath());
                } else {
                    // default transport
                }
            }
        }

        return parent::send($transport);
    }

    public static function getSenderFromConfig()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = Kwf_Registry::get('config')->server->domain;
        }
        $hostNonWww = preg_replace('#^www\\.#', '', $host);
        return array(
            'address' => str_replace('%host%', $hostNonWww, Kwf_Registry::get('config')->email->from->address),
            'name' => str_replace('%host%', $hostNonWww, Kwf_Registry::get('config')->email->from->name)
        );
    }
}
