import xlrd as ex
import os,re
import sys

# Save data into csv format.
def saveData(sheet, filename):
    if sheet.nrows==0 and sheet.ncols ==0:
        return
    filename = filename[:filename.find(".xls")].replace("/","_").replace(" ","")+"_"+sheet.name.replace(" ","")+".txt"
  
    outFile = open(filename,"w")
   
    outList = []
    for i in range(sheet.nrows):
        temp = ""
        for j in range(sheet.ncols):
            #print sheet.cell_value(i,j)
            temp+=str(sheet.cell_value(i,j))+"\t"
        temp+="\n"
        print temp
        outList.append(temp)
    outFile.writelines(outList)
    print "here"
    outFile.close()

def manipulateHouse(sheet):
    
    pass
def findFieldName(root,book,outList):
    for i in range (book.nsheets):
      sheet = book.sheet_by_index(i)
      header = 0
      row = 0
      for i in range(sheet.nrows):
        for j in range(sheet.ncols):
            if (re.search("\w",sheet.cell_value(i,j))):
                header +=1
           
        if header==sheet.ncols or (header>=2 and header>sheet.ncols*2/3):
            row = i
            break
        
   
    if header==sheet.ncols or (header>=2 and header>sheet.ncols*2/3):
        attribute = root+":\t"
        for t in range(sheet.ncols):
            if re.search("\w",sheet.cell_value(row,t)):
                if not (str(sheet.cell_value(row,t))+"\n" in outList):
                    attribute+=str(sheet.cell_value(row,t))+"\t"
        outList.append(attribute+"\n")


    
def main():
    #if (len(sys.argv)!=2):
     #   print "Wrong usage"
    
    directory = "Energy Data"
   
    # walk through all the folders from the given directory - starting point.
    outList = []
    outfile = open("fieldname","w")
    for root,dirs,files in os.walk(directory):
   
        for filename in files:
            try:
                if re.search(".xls",filename):
                    #print filename
                    full_name=os.path.join(root,filename)
                    book=ex.open_workbook(full_name)
                    
                    findFieldName(root,book,outList)
            except:
               continue

    for attribute in outList:
        print attribute
    outfile.writelines(outList)
    outfile.close()
if __name__=="__main__":
    main()
