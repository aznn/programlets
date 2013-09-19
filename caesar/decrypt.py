#!/usr/env python

def rot(string, val):
    def rotc(c):
        if c.isalpha():
            return chr((ord(c) - ord('a') + val) % 26 + ord('a'))
        else:
            return c

    return ''.join([rotc(c) for c in string])


def allPossibilities(string):
    # returns all possible words from the starting of the line
    def beginWords(string):
        for i in range(0, len(string)+1):
            if i < 45 and string[:i] in words:
                yield (string[:i], i)

    def solve(s, phrase, sentences):
        if len(s) == 0:
            sentences += [' '.join(phrase)]

        for word, l in beginWords(s):
            solve(s[l:], phrase + [word], sentences)

    sentences = []
    solve(string, [], sentences)
    return sentences


with open('words', 'r') as f:
    words = set([word.strip().lower() for word in f.readlines()])


print "Enter encrypted sentence"
# sanitize, remove spaces
cypher = ''.join(raw_input().strip().lower().split())
res = []
for i in range(0, 26):
    if len(allPossibilities(rot(cypher, i))) != 0:
        res.append(i)

for i in res:
    plain = rot(cypher, i)
    print "\nROT%d = %s" % (26-i, plain)
    for sentence in allPossibilities(plain):
        print "\t%s" % sentence

if len(res) == 0:
    print "No results"
