using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using System;
using UnityEngine.Networking;

struct InteractionReport {
    //Variable declaration
    //Note: I'm explicitly declaring them as public, but they are public by default. You can use private if you choose.
    public string prolificId;
    public string studyId;
    public string sessionId;
    public string domainURL;
    public string msg;

    public InteractionReport(string prolificId, string msg) {
        this.prolificId = prolificId;
        this.msg = msg;
        this.studyId = "default";
        this.sessionId = "default";
        this.domainURL = "default";
    }
}

public static class QueryHelper
{
    // dev
    //public static string serverURL = "https://d3game.dev.isr.umich.edu/service.php";

    // testing
    //public static string serverURL = "http://localhost/LuckingOut/service.php";

    // adaptable
    public static string serverURL = Application.absoluteURL.Split('?')[0] + "service.php";


    public static IEnumerator record(string msg)
    {
        //byte[] myData = System.Text.Encoding.UTF8.GetBytes("This is some test data");
        string url = Application.absoluteURL;
        string[] urlSplit = url.Split('?');
        string paramsString = "";
        string prolificId = "default";
        string studyId = "default";
        string sessionId = "default";
        string domainURL = urlSplit[0];


        // example

        /*
        https://umich.qualtrics.com/jfe/form/SV_4IoSJSkNUKqxOl0?PROLIFIC_PID=63dd5ec01d3544fa8e02d54d&STUDY_ID=63e14c745dea11fca5cff7c8&SESSION_ID=0wa5514d6rl

        https://www-personal.umich.edu/~peiyaoh/LuckingOut/?PROLIFIC_PID=63dd5ec01d3544fa8e02d54d&amp;STUDY_ID=63e14c745dea11fca5cff7c8&amp;SESSION_ID=0wa5514d6rl
        */


        if(urlSplit.Length > 1){
            paramsString = urlSplit[1];
            string[] paramSplit = paramsString.Split("&");
            if(paramSplit.Length > 0){
                // PROLIFIC_PID
                prolificId = paramSplit[0].Split("=")[1];
            }
            if(paramSplit.Length > 1){
                // STUDY_ID
                studyId = paramSplit[1].Split("=")[1];
            }
            if(paramSplit.Length > 1){
                // SESSION_ID
                sessionId = paramSplit[2].Split("=")[1];
            }
        }

        // Reference code
        /*
        var jsonString = JsonUtility.ToJson(jsonData) ?? "";
        UnityWebRequest request = UnityWebRequest.Put(url, jsonString);
        request.SetRequestHeader("Content-Type", "application/json");
        yield return request.Send();
        */

        Debug.Log("Query Params - prolificId: " + prolificId);
        Debug.Log("Query Params - studyId: " + studyId);
        Debug.Log("Query Params - sessionId: " + sessionId);
        Debug.Log("Query Params - domainURL: " + domainURL);
        

        Debug.Log("Params - msg: " + msg);


        InteractionReport interactionReport = new InteractionReport(prolificId, msg);
        interactionReport.studyId = studyId;
        interactionReport.sessionId = sessionId;
        interactionReport.domainURL = domainURL;

        Debug.Log("interactionReport.prolificId: " + interactionReport.prolificId);
        Debug.Log("interactionReport.msg: " + interactionReport.msg);
        Debug.Log("interactionReport.studyId: " + interactionReport.studyId);
        Debug.Log("interactionReport.sessionId: " + interactionReport.sessionId);
        Debug.Log("interactionReport.domainURL: " + interactionReport.domainURL);
        

        


        string jsonString =  JsonUtility.ToJson(interactionReport);
        // '{"studyId": "' + studyId + '", "msg": "'+ msg +'"}';  //

        Debug.Log("jsonString: " + jsonString);


        // using (UnityWebRequest www = UnityWebRequest.Put("http://localhost/LuckingOut/service.php", studyId + "\t"+ msg))
        //using (UnityWebRequest www = UnityWebRequest.Post("https://d3game.dev.isr.umich.edu/service.php", jsonString))

        using (UnityWebRequest www = UnityWebRequest.Post(QueryHelper.serverURL, jsonString))
        {
            // added, but does not work in deployment space
            //www.SetRequestHeader("Content-Type", "application/json");

            // application/x-www-form-urlencoded
            www.SetRequestHeader("Content-Type", "application/x-www-form-urlencoded");


            yield return www.SendWebRequest();

            if (www.result != UnityWebRequest.Result.Success)
            {
                Debug.Log(www.error);
            }
            else
            {
                Debug.Log("Upload complete!");
            }
        }
    }
}
