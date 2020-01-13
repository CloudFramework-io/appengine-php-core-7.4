<?php
class Test extends Tests
{
    /** @var DataStore $ds */
    var $ds = null;
    function main() {
        $this->wants('Test DataStore Connections');
        $this->says('Env Vars');
        $this->says("  * getenv('DATASTORE_EMULATOR_HOST'): ".((getenv('DATASTORE_EMULATOR_HOST'))?getenv('DATASTORE_EMULATOR_HOST'):'not defined'));
        $this->says("  * getenv('GOOGLE_APPLICATION_CREDENTIALS'): ".((getenv('GOOGLE_APPLICATION_CREDENTIALS'))?getenv('GOOGLE_APPLICATION_CREDENTIALS'):'not defined'));

        if(!getenv('DATASTORE_EMULATOR_HOST') && !getenv('GOOGLE_APPLICATION_CREDENTIALS')) {

            $this->says("FOR: DATASTORE_EMULATOR_HOST");
            $this->says("    We recommend execute the followings commands:");
            $this->says("    - gcloud components install cloud-datastore-emulator");
            $this->says("    - gcloud beta emulators datastore start");
            $this->says("    - $(gcloud beta emulators datastore env-init)");
            $this->says("      # More info in: https://cloud.google.com/datastore/docs/tools/datastore-emulator");
            $this->says("");

            $this->says("FOR: GOOGLE_APPLICATION_CREDENTIALS");
            $this->says("    With no emulator you need to specify GOOGLE_APPLICATION_CREDENTIALS");
            $this->says("    # More info in: https://cloud.google.com/datastore/docs/reference/libraries");
            $this->says("    export GOOGLE_APPLICATION_CREDENTIALS=~/credentials/datastore-cloudframwork-cloud-service-account.json");


            $this->says("");
            $this->addsError('Missing Env. Vars: DATASTORE_EMULATOR_HOST and GOOGLE_APPLICATION_CREDENTIALS');

        }
        $this->test1();
        $this->ends();
    }
    private function test1(){
        $schema = '
        {
            "title": ["string","index"],
            "author": ["string","index"],
            "published": ["date","index"],
            "cat":["list","index"],
            "description": ["string"],
            "dateinsertion": ["datetime","index"],
            "data": ["json"],
            "zipdata": ["zip"]
        }';
        $this->says('Init DataStore EntityTest in spacename SpaceNameTest with schema: ');
        $this->says($schema);
        $this->ds = $this->core->loadClass('DataStore',['EntityTest','SpaceNameTest',json_decode($schema,true)]);
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }

        $this->says('Inserting entities ');
        $data[] = ['title'=>'title1',"dateinsertion"=>"now","cat"=>['cat1','cat2'],'data'=>['var1'=>"value1"],'zipdata'=>'zipdata1'];
        $data[] = ['title'=>'title2',"dateinsertion"=>"2018-01-01","cat"=>['cat1','cat2'],'data'=>['var1'=>"value1"],'zipdata'=>'zipdata2'];
        $ret = $this->ds->createEntities($data);
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            $this->says(' - ok');
            $this->says($ret);
        }

        $this->says('Reading entities with no transformation ');
        $this->ds->transformReadedEntities = false;
        $ret = $this->ds->fetchAll();
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            $this->says(' - ok');
            $this->says($ret);
        }

        $this->says('Reading entities with transformation (default)');
        $this->ds->transformReadedEntities = true;
        $ret = $this->ds->fetchAll();
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            $this->says(' - ok');
            $this->says($ret);
        }



        $this->says('Deleting entities where: title=>"title1"');
        $ret = $this->ds->delete(['title'=>'title1']);
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            $this->says(' - ok');
            $this->says($ret);
        }

        $this->says('Reading entities ');
        $ret = $this->ds->fetchAll();
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            $this->says(' - ok');
            $this->says($ret);
        }

        $this->says('deleteByKeys: ');
        foreach ($ret as $item) {
            $this->says('  - KeyId = '.$item['KeyId']);
            $ret = $this->ds->deleteByKeys($item['KeyId']);
            if($this->ds->error) {
                $this->addsError($this->ds->errorMsg);
                $this->says(' - failed');
                return;
            }
            else {
                $this->says(' - ok');
            }
        }
        $this->says('Reading entities ');
        $ret = $this->ds->fetchAll();
        if($this->ds->error) {
            $this->addsError($this->ds->errorMsg);
            $this->says(' - failed');
            return;
        }
        else {
            if($ret) {

            } else {
                $this->says(' - ok 0 entities');
            }
            $this->says($ret);
        }
    }
}
