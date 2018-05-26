package extract_links;

import org.jsoup.Jsoup;
import org.jsoup.nodes.*;
import org.jsoup.select.Elements;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.FilenameFilter;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

public class ExtractLinks {
   public static void main(String [] args) throws Exception{
	   // the directory of html files
	   // args[1]: the file containing the mapping information
	   // args[2]: the file to write the edge information
	 
	   File dir = new File(args[0]);
	   System.out.println(dir.getAbsolutePath());
	   File storageFolder = new File(args[2]);
       if (!storageFolder.exists()) storageFolder.createNewFile();
	   
       BufferedWriter writer= new BufferedWriter( new FileWriter(args[2],true));
	   BufferedReader in= new BufferedReader (new FileReader(args[1]));
	   Map<String, String> fileUrlMap = new HashMap<String,String>();
	   Map<String, String> urlfileMap = new HashMap<String,String>();
	   
	   // map reading
	   String str;
       while ((str = in.readLine()) != null) {
          String [] s = str.split(",");
          if ( urlfileMap.containsKey(s[1])) { continue;}
          fileUrlMap.put(s[0], s[1]);
          urlfileMap.put(s[1], s[0]);
        }
	   Set<String> edges = new HashSet<String>();
	   FilenameFilter htmlFilter = new FilenameFilter() {
			public boolean accept(File dir, String name) {
				String lowercaseName = name.toLowerCase();
				if (lowercaseName.endsWith(".html")) {
					return true;
				} else {
					return false;
				}
			}
		};
	   
	   
	   for (File file: dir.listFiles(htmlFilter)) {
		   String filename = file.getName();
		   //System.out.println(filename);
		   if (!fileUrlMap.containsKey(file.getName())) {
			   if(file.delete()) { System.out.format("delete file:%s\n", filename);
			   continue;}
			}
		   Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
		   Elements links = doc.select("a[href]");
		   Elements pngs = doc.select("[src]");
		   for (Element link: links) {
			   String url = link. attr("href").trim();
			   if(urlfileMap.containsKey(url)) {
				   edges.add( filename+" "+ urlfileMap.get(url));
			   }
		   }
		   
	   }
	   
	  
	   for ( String s: edges) {
		   writer.write(s);
		   writer.newLine();
	   }
	   writer.flush();
	   writer.close();
   }
}
