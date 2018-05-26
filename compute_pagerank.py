import networkx as nx
import sys

def main(argv):
	f1=open(argv[3], "w+")
	G= nx.read_edgelist(argv[2], create_using=nx.DiGraph())
	pr = nx.pagerank(G , alpha=0.85, personalization= None, max_iter=100, tol=1e-06, nstart=None, weight='weight', dangling=None)
	s = argv[1]
	
	for key, value in pr.items():
		f1.write(  '%s%s=%s\n' % (s, key, value) )
	f1.close()


if __name__ == "__main__":
    sys.exit(main(sys.argv))
