<?php

namespace BFW\Test\Bin\ModuleManager;

use Exception;
use bultonFr\Utils\Cli\BasicMsg;
use BFW\Test\Bin\Ressources\LogLineTesterTrait;

class ReinstallOne extends AbstractModuleManagerTests
{
    use LogLineTesterTrait;

    protected function testsList(): array
    {
        return [
            [$this, 'checkCmdOutput'],
            [$this, 'checkMonologRecords']
        ];
    }

    protected function checkCmdOutput(): bool
    {
        BasicMsg::displayMsg('> Check command output : ', 'yellow');

        $cmd       = 'cd '.$this->installDir.' && ./vendor/bin/bfwAddMod -r -- bfw-test-install';
        $cmdOutput = $this->execCmd($cmd);

        $expectedOutput = ""
            ."\033[0;33m> Delete module bfw-test-install ... \033[0m\033[0;32mDone\033[0m\n"
            ."\033[0;33m> Add module bfw-test-install ... \033[0m\033[0;32mDone\033[0m\n"
            ."\033[0;33m> Execute install script for bfw-test-install ... \033[0m"
                ."  \033[1;33mCreate install_test.php file into web directory\033[0m\n"
            ."\033[0;32mDone\033[0m\n"
        ;

        if ($cmdOutput === $expectedOutput) {
            BasicMsg::displayMsgNL('OK', 'green', 'bold');
            return true;
        }

        BasicMsg::displayMsgNL('Fail', 'red', 'bold');
        return false;
    }

    protected function checkMonologRecords(): bool
    {
        BasicMsg::displayMsg('> Check bfw logs : ', 'yellow');

        $this->logRecords = $this->obtainMonologRecords($this->logFilePath);
        if (count($this->logRecords) !== 12) {
            BasicMsg::displayMsgNL('Fail : Number of line not equal to 12', 'red', 'bold');
            return false;
        }

        try {
            //Line 0 [2019-05-17 10:33:26] bfw.DEBUG: Module - Read module info {"name":"bfw-test-install","path":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install"} []
            $lineNb = 0;
            $this->checkLogLineMsg($lineNb, 'Module - Read module info');
            $this->checkLogLineContextKeys($lineNb, ['name', 'path']);
            $this->checkLogLineContextKeyEqual($lineNb, 'name', 'bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'path', '/test/install/app/modules/available/bfw-test-install');

            //Line 1 [2019-05-17 10:33:26] bfw.DEBUG: FileManager - Remove symlink {"linkFile":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Remove symlink');
            $this->checkLogLineContextKeys($lineNb, ['linkFile']);
            $this->checkLogLineContextKeyContain($lineNb, 'linkFile', '/test/install/app/modules/available/bfw-test-install');

            //Line 2 [2019-05-17 10:33:26] bfw.DEBUG: Module - Delete config files {"name":"bfw-test-install","configPath":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'Module - Delete config files');
            $this->checkLogLineContextKeys($lineNb, ['name', 'configPath']);
            $this->checkLogLineContextKeyEqual($lineNb, 'name', 'bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'configPath', '/test/install/app/config/bfw-test-install');

            //Line 3 [2019-05-17 10:33:26] bfw.DEBUG: FileManager - Remove files and directories {"path":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Remove files and directories');
            $this->checkLogLineContextKeys($lineNb, ['path']);
            $this->checkLogLineContextKeyContain($lineNb, 'path', '/test/install/app/config/bfw-test-install');

            //Line 4 [2019-05-17 09:00:49] bfw.DEBUG: Module - Read module info {"name":"bfw-test-install","path":"/opt/Projects/bfw/bfw/test/install/vendor//bulton-fr/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'Module - Read module info');
            $this->checkLogLineContextKeys($lineNb, ['name', 'path']);
            $this->checkLogLineContextKeyEqual($lineNb, 'name', 'bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'path', '/test/install/vendor//bulton-fr/bfw-test-install');

            //Line 5 [2019-05-17 09:00:49] bfw.DEBUG: FileManager - Create symlink {"linkTarget":"/opt/Projects/bfw/bfw/test/install/vendor//bulton-fr/bfw-test-install","linkFile":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Create symlink');
            $this->checkLogLineContextKeys($lineNb, ['linkTarget', 'linkFile']);
            $this->checkLogLineContextKeyContain($lineNb, 'linkTarget', '/test/install/vendor//bulton-fr/bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'linkFile', '/test/install/app/modules/available/bfw-test-install');

            //Line 6 [2019-05-22 22:26:23] bfw.DEBUG: FileManager - Create symlink - Use relative path {"target":"../../../vendor//bulton-fr/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Create symlink - Use relative path');
            $this->checkLogLineContextKeys($lineNb, ['target']);
            $this->checkLogLineContextKeyContain($lineNb, 'target', '../../../vendor//bulton-fr/bfw-test-install');

            //Line 7 [2019-05-17 09:00:49] bfw.DEBUG: Module - Copy config files {"name":"bfw-test-install","configPath":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install","sourceConfigPath":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install/config/","configFiles":["test-install.json"]} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'Module - Copy config files');
            $this->checkLogLineContextKeys($lineNb, ['name', 'configPath', 'sourceConfigPath', 'configFiles']);
            $this->checkLogLineContextKeyEqual($lineNb, 'name', 'bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'configPath', '/test/install/app/config/bfw-test-install');
            $this->checkLogLineContextKeyContain($lineNb, 'sourceConfigPath', '/test/install/app/modules/available/bfw-test-install/config/');
            $this->checkLogLineContextKeyEqual($lineNb, 'configFiles', ["test-install.json"]);

            //Line 8 [2019-05-17 09:00:49] bfw.DEBUG: FileManager - Create directory {"path":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Create directory');
            $this->checkLogLineContextKeys($lineNb, ['path']);
            $this->checkLogLineContextKeyContain($lineNb, 'path', '/test/install/app/config/bfw-test-install');

            //Line 9 [2019-05-17 09:00:49] bfw.DEBUG: FileManager - Copy file {"source":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install/config/manifest.json","target":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install/manifest.json"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Copy file');
            $this->checkLogLineContextKeys($lineNb, ['source', 'target']);
            $this->checkLogLineContextKeyContain($lineNb, 'source', '/test/install/app/modules/available/bfw-test-install/config/manifest.json');
            $this->checkLogLineContextKeyContain($lineNb, 'target', '/test/install/app/config/bfw-test-install/manifest.json');

            //Line 10 [2019-05-17 09:00:49] bfw.DEBUG: FileManager - Copy file {"source":"/opt/Projects/bfw/bfw/test/install/app/modules/available/bfw-test-install/config/test-install.json","target":"/opt/Projects/bfw/bfw/test/install/app/config/bfw-test-install/test-install.json"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'FileManager - Copy file');
            $this->checkLogLineContextKeys($lineNb, ['source', 'target']);
            $this->checkLogLineContextKeyContain($lineNb, 'source', '/test/install/app/modules/available/bfw-test-install/config/test-install.json');
            $this->checkLogLineContextKeyContain($lineNb, 'target', '/test/install/app/config/bfw-test-install/test-install.json');

            //Line 11 [2019-05-17 09:00:49] bfw.DEBUG: Module - Run install script {"name":"bfw-test-install","installScript":"install.php"} []
            $lineNb++;
            $this->checkLogLineMsg($lineNb, 'Module - Run install script');
            $this->checkLogLineContextKeys($lineNb, ['name', 'installScript']);
            $this->checkLogLineContextKeyEqual($lineNb, 'name', 'bfw-test-install');
            $this->checkLogLineContextKeyEqual($lineNb, 'installScript', 'install.php');
        } catch (Exception $e) {
            BasicMsg::displayMsgNL('Fail : '.$e->getMessage(), 'red', 'bold');
            return false;
        }

        BasicMsg::displayMsgNL('OK', 'green', 'bold');
        return true;
    }
}
